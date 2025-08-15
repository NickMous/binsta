<?php

namespace NickMous\Binsta\Internals\Seeders;

abstract class Seeder
{
    abstract public function run(): void;

    /** @param string|array<int, string> $seederClasses */
    protected function call(string|array $seederClasses): void
    {
        $seeders = is_array($seederClasses) ? $seederClasses : [$seederClasses];

        foreach ($seeders as $seederClass) {
            if (!class_exists($seederClass)) {
                throw new \InvalidArgumentException("Seeder class {$seederClass} does not exist");
            }

            $seeder = new $seederClass();
            if (!$seeder instanceof self) {
                throw new \InvalidArgumentException("Class {$seederClass} must extend " . self::class);
            }

            echo "Seeding: {$seederClass}...\n";
            $seeder->run();
            echo "Seeded: {$seederClass}\n";
        }
    }
}
