<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Color;
use App\Repository\ColorRepository;
use App\Service\ColorService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ColorServiceTest extends TestCase
{
    private ColorRepository $colorRepository;
    private ColorService $colorService;

    protected function setUp(): void
    {
        $this->colorRepository = $this->createMock(ColorRepository::class);
        $this->colorService = new ColorService($this->colorRepository);
    }

    /**
     * Test getAllColors returns empty collection when no colors exist
     */
    public function testGetAllColorsReturnsEmptyCollectionWhenNoColors(): void
    {
        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->colorService->getAllColors();

        $this->assertCount(0, $result);
    }

    /**
     * Test getAllColors returns all colors with correct mapping
     */
    public function testGetAllColorsReturnsAllColorsWithCorrectMapping(): void
    {
        $color1 = $this->createColorStub(1, 'Rouge', '#FF0000');
        $color2 = $this->createColorStub(2, 'Bleu', '#0000FF');
        $color3 = $this->createColorStub(3, 'Vert', '#00FF00');

        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$color1, $color2, $color3]);

        $result = $this->colorService->getAllColors();

        $this->assertCount(3, $result);

        $colors = $result->toArray();

        $this->assertEquals(1, $colors[0]['id']);
        $this->assertEquals('Rouge', $colors[0]['name']);
        $this->assertEquals('#FF0000', $colors[0]['hexCode']);

        $this->assertEquals(2, $colors[1]['id']);
        $this->assertEquals('Bleu', $colors[1]['name']);
        $this->assertEquals('#0000FF', $colors[1]['hexCode']);
    }

    /**
     * Test getAllColors maps color properties correctly
     */
    public function testGetAllColorsMapsPropertiesCorrectly(): void
    {
        $color = $this->createColorStub(10, 'Noir', '#000000');

        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$color]);

        $result = $this->colorService->getAllColors();

        $colors = $result->toArray();

        $this->assertArrayHasKey('id', $colors[0]);
        $this->assertArrayHasKey('name', $colors[0]);
        $this->assertArrayHasKey('hexCode', $colors[0]);
        $this->assertCount(3, $colors[0]); // Only 3 fields
    }

    /**
     * Test getColorById returns null when color not found
     */
    public function testGetColorByIdReturnsNullWhenNotFound(): void
    {
        $this->colorRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $result = $this->colorService->getColorById(999);

        $this->assertNull($result);
    }

    /**
     * Test getColorById returns color data when found
     */
    public function testGetColorByIdReturnsColorDataWhenFound(): void
    {
        $color = $this->createColorStub(5, 'Jaune', '#FFFF00');

        $this->colorRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($color);

        $result = $this->colorService->getColorById(5);

        $this->assertIsArray($result);
        $this->assertEquals(5, $result['id']);
        $this->assertEquals('Jaune', $result['name']);
        $this->assertEquals('#FFFF00', $result['hexCode']);
    }

    /**
     * Test getColorById maps all properties correctly
     */
    public function testGetColorByIdMapsAllPropertiesCorrectly(): void
    {
        $color = $this->createColorStub(7, 'Blanc', '#FFFFFF');

        $this->colorRepository
            ->expects($this->once())
            ->method('find')
            ->with(7)
            ->willReturn($color);

        $result = $this->colorService->getColorById(7);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('hexCode', $result);
        $this->assertCount(3, $result); // Only 3 fields
    }

    /**
     * Test searchByName returns empty collection when no match
     */
    public function testSearchByNameReturnsEmptyCollectionWhenNoMatch(): void
    {
        $color1 = $this->createColorStub(1, 'Rouge', '#FF0000');
        $color2 = $this->createColorStub(2, 'Bleu', '#0000FF');

        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$color1, $color2]);

        $result = $this->colorService->searchByName('Vert');

        $this->assertCount(0, $result);
    }

    /**
     * Test searchByName filters colors by name case-insensitive
     */
    public function testSearchByNameFiltersCaseInsensitive(): void
    {
        $color1 = $this->createColorStub(1, 'Rouge', '#FF0000');
        $color2 = $this->createColorStub(2, 'Bleu', '#0000FF');
        $color3 = $this->createColorStub(3, 'Rouge Foncé', '#8B0000');

        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$color1, $color2, $color3]);

        $result = $this->colorService->searchByName('rouge');

        $colors = $result->toArray();

        $this->assertCount(2, $colors);
        $this->assertEquals('Rouge', $colors[0]['name']);
        $this->assertEquals('Rouge Foncé', $colors[1]['name']);
    }

    /**
     * Test searchByName handles partial matches
     */
    public function testSearchByNameHandlesPartialMatches(): void
    {
        $color1 = $this->createColorStub(1, 'Bleu Clair', '#ADD8E6');
        $color2 = $this->createColorStub(2, 'Bleu Foncé', '#00008B');
        $color3 = $this->createColorStub(3, 'Rouge', '#FF0000');

        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$color1, $color2, $color3]);

        $result = $this->colorService->searchByName('Bleu');

        $colors = $result->toArray();

        $this->assertCount(2, $colors);
        $this->assertEquals('Bleu Clair', $colors[0]['name']);
        $this->assertEquals('Bleu Foncé', $colors[1]['name']);
    }

    /**
     * Test searchByName returns all matching colors with correct mapping
     */
    public function testSearchByNameReturnsCorrectMapping(): void
    {
        $color1 = $this->createColorStub(1, 'Noir Mat', '#000000');
        $color2 = $this->createColorStub(2, 'Rouge', '#FF0000');
        $color3 = $this->createColorStub(3, 'Noir Brillant', '#000001');

        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$color1, $color2, $color3]);

        $result = $this->colorService->searchByName('Noir');

        $colors = $result->toArray();

        $this->assertCount(2, $colors);

        $this->assertEquals(1, $colors[0]['id']);
        $this->assertEquals('Noir Mat', $colors[0]['name']);
        $this->assertEquals('#000000', $colors[0]['hexCode']);

        $this->assertEquals(3, $colors[1]['id']);
        $this->assertEquals('Noir Brillant', $colors[1]['name']);
        $this->assertEquals('#000001', $colors[1]['hexCode']);
    }

    /**
     * Test searchByName re-indexes collection after filtering
     */
    public function testSearchByNameReIndexesCollection(): void
    {
        // Create colors where matching ones are not consecutive
        $color1 = $this->createColorStub(1, 'Rouge', '#FF0000');
        $color2 = $this->createColorStub(2, 'Bleu', '#0000FF');
        $color3 = $this->createColorStub(3, 'Vert', '#00FF00');
        $color4 = $this->createColorStub(4, 'Bleu Clair', '#ADD8E6');

        $this->colorRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$color1, $color2, $color3, $color4]);

        $result = $this->colorService->searchByName('Bleu');

        $colors = $result->toArray();

        // After filtering, we should have consecutive keys 0, 1 (not 1, 3)
        $this->assertArrayHasKey(0, $colors);
        $this->assertArrayHasKey(1, $colors);
        $this->assertArrayNotHasKey(2, $colors);
    }

    /**
     * Helper method to create a Color stub with predefined values
     */
    private function createColorStub(int $id, string $name, string $hexCode): Color
    {
        $color = $this->createStub(Color::class);

        $color->method('getId')->willReturn($id);
        $color->method('getName')->willReturn($name);
        $color->method('getHexCode')->willReturn($hexCode);

        return $color;
    }
}
