<?php declare(strict_types=1);

namespace src\Core\Content\Test\Media\Pathname;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\Pathname\PathnameStrategy\Md5PathnameStrategy;
use Shopware\Core\Content\Media\Pathname\PathnameStrategy\PathnameStrategyInterface;
use Shopware\Core\Content\Media\Pathname\PathnameStrategy\UuidPathnameStrategy;
use Shopware\Core\Content\Test\Media\MediaFixtures;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class PathnameStrategyTest extends TestCase
{
    use IntegrationTestBehaviour;
    use MediaFixtures;

    public function testUuidCacheBuster(): void
    {
        $this->assertCacheBusterGenerator($this->getUuidPathnameStrategy());
    }

    public function testUuidFilename(): void
    {
        $this->assertFilenameGenerator($this->getUuidPathnameStrategy());
    }

    public function testUuidEncoding(): void
    {
        $this->assertHashGenerator($this->getUuidPathnameStrategy(), $this->getJpgWithFolder(), 33);
        $this->assertHashGenerator($this->getUuidPathnameStrategy(), $this->getJpg(), 33);
        $this->assertHashGenerator($this->getUuidPathnameStrategy(), $this->getTxt(), 33);
    }

    public function testMd5CacheBuster(): void
    {
        $this->assertCacheBusterGenerator($this->getMd5PathnameStrategy());
    }

    public function testMd5Filename(): void
    {
        $this->assertFilenameGenerator($this->getMd5PathnameStrategy());
    }

    public function testMd5Encoding(): void
    {
        $this->assertHashGenerator($this->getMd5PathnameStrategy(), $this->getJpg(), 8);
        $this->assertHashGenerator($this->getMd5PathnameStrategy(), $this->getJpgWithFolder(), 8);
        $this->assertHashGenerator($this->getMd5PathnameStrategy(), $this->getTxt(), 8);
    }

    private function getUuidPathnameStrategy(): UuidPathnameStrategy
    {
        return $this
            ->getContainer()
            ->get(UuidPathnameStrategy::class);
    }

    private function getMd5PathnameStrategy(): Md5PathnameStrategy
    {
        return $this
            ->getContainer()
            ->get(Md5PathnameStrategy::class);
    }

    private function assertHashGenerator(PathnameStrategyInterface $strategy, MediaEntity $media, int $length): void
    {
        $encoded = $strategy->generatePathHash($media);

        static::assertSame($encoded, $strategy->generatePathHash($media));
        static::assertStringEndsNotWith('/', $encoded);
        static::assertStringStartsNotWith('/', $encoded);
        static::assertSame($length, mb_strlen($encoded));
    }

    private function assertCacheBusterGenerator(PathnameStrategyInterface $strategy): void
    {
        static::assertNull($strategy->generatePathCacheBuster($this->getMediaWithManufacturer()));
        static::assertSame('1293894181', $strategy->generatePathCacheBuster($this->getPngWithoutExtension()));
    }

    private function assertFilenameGenerator(PathnameStrategyInterface $strategy): void
    {
        $jpg = $this->getJpg();
        $mediaWithThumbnail = $this->getMediaWithThumbnail();

        static::assertSame('jpgFileWithExtension.jpg', $strategy->generatePhysicalFilename($jpg));
        static::assertSame('jpgFileWithExtension_200x200.jpg', $strategy->generatePhysicalFilename($jpg, $mediaWithThumbnail->getThumbnails()->first()));
    }
}
