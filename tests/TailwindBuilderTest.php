<?php

/*
 * This file is part of the SymfonyCasts TailwindBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonycasts\TailwindBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfonycasts\TailwindBundle\TailwindBuilder;

class TailwindBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__.'/fixtures/var/tailwind');
    }

    public function testIntegration(): void
    {
        $builder = new TailwindBuilder(
            __DIR__.'/fixtures',
            __DIR__.'/fixtures/app.css',
            __DIR__.'/fixtures/var/tailwind'
        );
        $process = $builder->runBuild(false);
        $process->wait();

        $this->assertTrue($process->isSuccessful());
        $this->assertFileExists(__DIR__.'/fixtures/var/tailwind/tailwind.built.css');
    }
}
