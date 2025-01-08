<?php

namespace App\Hydrator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator as BaseAbstractHydrator;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class AbstractHydrator extends BaseAbstractHydrator
{
    protected PropertyAccessor $propertyAccessor;

    protected PropertyInfoExtractor $propertyInfo;

    protected array $objectProperties = [];

    protected array $dates = [];

    public function __construct(EntityManagerInterface $em, private readonly string $dtoClass)
    {
        parent::__construct($em);
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicMethods()
            ->getPropertyAccessor();

        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $listExtractors = [$reflectionExtractor];
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];
        $descriptionExtractors = [$phpDocExtractor];
        $accessExtractors = [$reflectionExtractor];
        $propertyInitializableExtractors = [$reflectionExtractor];

        $this->propertyInfo = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors,
            $propertyInitializableExtractors
        );

        $this->buildObjectPropertiesTypes();
    }

    protected function buildObjectPropertiesTypes(): void
    {
        $this->buildPropertiesTypesForObject($this->dtoClass);
    }

    protected function buildPropertiesTypesForObject(string $dto): void
    {
        $properties = $this->propertyInfo->getProperties($dto);
        foreach ($properties as $property) {
            $propertyTypes = $this->propertyInfo->getTypes($dto, $property);
            $firstProperty = reset($propertyTypes);

            $class = $firstProperty->isCollection()
                ? $firstProperty->getCollectionValueTypes()[0]->getClassName()
                : null;

            if (null === $class) {
                $this->objectProperties[$dto][$property] =
                    $firstProperty->getClassName() ?? $firstProperty->getBuiltinType();

                continue;
            }

            $this->objectProperties[$dto][$property] = $class;
            $this->buildPropertiesTypesForObject($class);
        }
    }

    protected function getValue(string $key, mixed $value, string $dtoClass = null): mixed
    {
        if (isset($this->objectProperties[$dtoClass]) && isset($this->objectProperties[$dtoClass][$key])) {
            if ($this->objectProperties[$dtoClass][$key] === \DateTimeInterface::class) {
                $str = substr($value, 0, 10);
                $date = $this->dates[$str] ?? new \DateTime($str);
                $this->dates[$str] = $date;

                return $date;
            }
        }

        return $value;
    }

    protected function hydrateAllData(): array
    {
        $results = $formatedResults = $properties = [];
        $associativeResults = $this->stmt->fetchAllAssociative();

        if ([] !== $associativeResults) {
            foreach ($associativeResults[0] as $key => $value) {
                $alias = explode('_', $this->rsm->getScalarAlias($key));
                $properties['key_to_alias'][$key] = $alias;
                $aliasToKey = null;
                $this->generateCollectionFromFlatArray($alias, $aliasToKey, $key, false);
                $properties['alias_to_key'] = array_merge_recursive($properties['alias_to_key'] ?? [], $aliasToKey);
            }
        }

        foreach ($associativeResults as $row) {
            $this->formatRow($row, $formatedResults, $properties);
        }

        foreach ($formatedResults as $formatedResult) {
            $this->hydrateRowData($formatedResult, $results);
        }

        return $results;
    }

    protected function hydrateRowData(array $row, array &$result): void
    {
        $result[] = $this->hydrateValue($this->dtoClass, $row);
    }

    protected function hydrateValue(string $class, array $row)
    {
        $object = new $class;
        foreach ($row as $property => $value) {
            if (is_array($value)) {
                $subEntries = $value;
                foreach ($subEntries as $entry) {
                    $object->{$property}[] = $this->hydrateValue($this->objectProperties[$class][$property], $entry);
                }

                continue;
            }

            $object->{$property} = $this->getValue($property, $value, $class);
        }

        return $object;
    }

    protected function formatRow(array $row, array &$result, $properties): void
    {
        $id = null;
        foreach ($properties['key_to_alias'] as $key => $property) {
            if (count($property) === 1) {
                if ('id' === $property[0]) {
                    $id = $row[$key];
                }

                $finalValue = $this->getValue($property[0], $row[$key], $this->dtoClass);
                $result[$id][$property[0]] = $finalValue;
                continue;
            }

            $temp = $properties['alias_to_key'];
            foreach ($property as $key) {
                $temp = &$temp[$key];
            }

            $rowKeyValue = $this->getInArray($property, $properties['alias_to_key']);
            $rowValue = $row[$rowKeyValue];
            if (null !== $rowValue) {
                $path = $this->buildPropertyPath($properties, $property, $row);
                $this->setInArray($result[$id], $path, $row[$rowKeyValue]);
            }
        }
    }

    private function setInArray(&$array, $path, $value): void
    {
        $temp = &$array;

        foreach ($path as $key) {
            $temp = &$temp[$key];
        }

        $temp = $value;
    }

    private function getInArray($path, $array): mixed
    {
        $temp = &$array;

        foreach ($path as $key) {
            $temp = &$temp[$key];
        }

        return $temp;
    }

    private function buildPropertyPath(array $properties, mixed $property, array $row): array
    {
        if (!is_array($property)) {
            return [];
        }

        $nbProperty = count($property) - 1;
        $iterator = 0;
        foreach ($property as $propertyStep) {
            $finalPath[] = $propertyStep;
            $propertyAliasToKeyPath[] = $propertyStep;
            $currentPropertyAliasToKeyPath = $propertyAliasToKeyPath;

            if ('id' !== $propertyStep && $iterator < $nbProperty) {
                $currentPropertyAliasToKeyPath[] = 'id';
                $finalPath[] = $row[$this->getInArray($currentPropertyAliasToKeyPath, $properties['alias_to_key'])];
            }

            $iterator++;
        }

        return $finalPath;
    }

    private function generateCollectionFromFlatArray(array $properties, mixed &$data, mixed $value, bool $renderLastItemAsArray = true): void
    {
        $firstLoop = true;
        for ($i = count($properties) - 1; $i >= 0; $i--) {
            $arr = [];
            if ($i == count($properties) - 1) {
                $arr[$properties[$i]] = $value;
            } else {
                $arr[$properties[$i]] = $newArr;
            }

            $newArr = $firstLoop && $renderLastItemAsArray ? [$arr] : $arr;
            $firstLoop = false;
        }

        $data = $newArr;
    }
}
