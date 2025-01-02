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
        if (isset($this->objectProperties[$dtoClass])) {
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
        $results = $formatedResults = [];
        $iterator = 1;

        foreach ($this->stmt->fetchAllAssociative() as $row) {
            $this->formatRow($row, $formatedResults, $iterator);
            $iterator++;
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

    protected function formatRow(array $row, array &$result, int $currentRowNumber): void
    {
        $id = null;
        foreach ($row as $key => $value) {
            if (null !== $finalValue = $value) {
                $properties = explode('_', $this->rsm->getScalarAlias($key));
                if (count($properties) > 0) {
                    if (count($properties) === 1) {
                        if ('id' === $properties[0]) {
                            $id = $finalValue;
                        }

                        $finalValue = $this->getValue($properties[0], $finalValue, $this->dtoClass);
                        $result[$id][$properties[0]] = $finalValue;
                        continue;
                    }

                    $collection = [];
                    $this->generateCollectionFromFlatArray($properties, $collection, $finalValue);
                    $collectionData = $collection[array_key_first($collection)];
                    $result[$id][$properties[0]][$currentRowNumber][array_key_first($collectionData)] = reset($collectionData);
                }
            }
        }
    }

    private function generateCollectionFromFlatArray($properties, &$data, $value)
    {
        for ($i = count($properties) - 1; $i >= 0; $i--) {
            $arr = [];
            if ($i == count($properties) - 1) {
                $arr[$properties[$i]] = $value;
            } else {
                $arr[$properties[$i]] = $newArr;
            }

            $newArr = $arr;
        }

        $data = $newArr;
    }
}
