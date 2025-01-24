<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'app:init:yaml')]
class LoadYamlContentCommand extends Command
{
    private const REFERENCE_TYPES = [
        ManyToOne::class,
        ManyToMany::class,
        OneToMany::class,
        OneToOne::class
    ];

    private array $objects = [];

    private array $parsedData = [];

    private array $dataWithDependencies = [];

    private array $dataDependenciesOrdered = [];

    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly EntityManagerInterface $em,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->yamlFiles() as $yaml) {
            $this->readYamlFiles($yaml);
        }

        $this->orderDependencies();

        foreach ($this->dataDependenciesOrdered as $orderDependency) {
            $newEntity = new $this->parsedData[$orderDependency]['class']();
            foreach ($this->parsedData[$orderDependency]['data'] as $propertyName => $datum) {
                $reflectedClass = new \ReflectionClass($newEntity);
                $this->process($reflectedClass, $newEntity, $propertyName, $datum);
            }

            $this->em->persist($newEntity);
            $this->em->flush();
            $this->objects[$orderDependency] = $newEntity;
        }

        foreach ($this->parsedData as $objectName => $parsedDatum) {
            if (true === isset($this->objects[$objectName])) {
                continue;
            }

            $newEntity = new $this->parsedData[$objectName]['class']();
            foreach ($this->parsedData[$objectName]['data'] as $propertyName => $datum) {
                $reflectedClass = new \ReflectionClass($newEntity);
                $this->process($reflectedClass, $newEntity, $propertyName, $datum);
            }

            $this->em->persist($newEntity);
            $this->em->flush();
            $this->objects[$objectName] = $newEntity;
        }

        $this->em->flush();


        return Command::SUCCESS;
    }

    private function isReference(\ReflectionClass $reflectedClass, string $propertyName): bool
    {
        foreach ($reflectedClass->getProperty($propertyName)->getAttributes() as $attribute) {
            if (in_array($attribute->getName(), self::REFERENCE_TYPES)) {
                return true;
            }
        }

        return false;
    }

    private function isFile(\ReflectionClass $reflect, string $propertyName): bool
    {
        return $reflect->getProperty($propertyName)->getType()->getName() === File::class;
    }

    private function process(\ReflectionClass $reflectionClass, object $entity, string $propertyName, $data): object
    {
        $dataToSet = $data;

        if ($this->isFile($reflectionClass, $propertyName)) {
            $file = $this->projectDir . '/var/import/files/' . $data;

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $baseName = basename($file, ".{extension}");
            $newFile = $this->projectDir . '/var/import/files/' . "{$baseName}_1.{$extension}";
            copy($file, $newFile);

            $dataToSet = new UploadedFile(path: $newFile, originalName: $data, test: true);
        }

        if ($this->isReference($reflectionClass, $propertyName)) {
            if (is_array($data)) {
                $dataToSet = [];

                foreach ($data as $datum) {
                    $dataToSet = array_merge($dataToSet, [$this->objects[substr($datum, 1)]]);
                }
            } else {
                $dataToSet = $this->objects[substr($data, 1)];
            }
        }

        $this->propertyAccessor->setValue($entity, $propertyName, $dataToSet);

        return $entity;
    }

    private function orderDependencies(int $countRemainingDependencies = -1): mixed
    {
        foreach ($this->dataWithDependencies as $entityName => $dependencies) {
            $hasDependency = false;
            foreach ($this->dataWithDependencies as $dataWithDependency) {
                $hasDependency = isset($dataWithDependency[substr($entityName, 1)]);
                if ($hasDependency) {
                    break;
                }
            }

            if (false === $hasDependency) {
                unset($this->dataWithDependencies[$entityName]);

                $this->dataDependenciesOrdered[] = substr($entityName, 1);
            }
        }

        $currentCountRemainingDependencies = count($this->dataWithDependencies);
        if ($currentCountRemainingDependencies === $countRemainingDependencies) {
            throw new \Exception('Circular or non-existent dependency');
        }

        if (0 !== $currentCountRemainingDependencies) {
            return $this->orderDependencies($currentCountRemainingDependencies);
        }

        return false;
    }

    private function yamlFiles(): array
    {
        $directory = $this->projectDir . '/var/import/content';

        return glob($directory . "/*.yaml");
    }

    private function readYamlFiles(string $yaml): void
    {
        $values = Yaml::parseFile($yaml);
        $entityClass = key($values);
        $reflect = new \ReflectionClass($entityClass);

        foreach ($values[$entityClass] as $entryName => $data) {
            $this->parsedData[$entryName]['class'] = $entityClass;
            $this->parsedData[$entryName]['data'] = $data;

            foreach ($data as $key => $datum) {
                if ($this->isReference($reflect, $key)) {
                    if (is_array($datum)) {
                        foreach ($datum as $subDatum) {
                            $this->dataWithDependencies[$subDatum][$entryName] = '';
                        }
                    } else {
                        $this->dataWithDependencies[$datum][$entryName] = '';
                    }
                }
            }
        }
    }
}
