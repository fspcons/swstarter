<?php

declare(strict_types=1);

namespace Tests\Unit\Entities;

use App\Domain\Entities\Film;
use PHPUnit\Framework\TestCase;

class FilmTest extends TestCase
{
    private function makeFilm(array $overrides = []): Film
    {
        return Film::fromSwapiProperties('1', array_merge([
            'title' => 'A New Hope',
            'opening_crawl' => 'It is a period of civil war...',
            'characters' => [
                'https://www.swapi.tech/api/people/1',
                'https://www.swapi.tech/api/people/2',
            ],
        ], $overrides));
    }

    public function test_creates_from_swapi_properties(): void
    {
        $film = $this->makeFilm();

        $this->assertEquals('1', $film->uid());
        $this->assertEquals('A New Hope', $film->title());
    }

    public function test_extracts_character_ids_from_urls(): void
    {
        $film = $this->makeFilm();

        $this->assertEquals(['1', '2'], $film->characterIds());
    }

    public function test_extracts_character_ids_from_urls_with_trailing_slashes(): void
    {
        $film = $this->makeFilm([
            'characters' => [
                'https://www.swapi.tech/api/people/10/',
                'https://www.swapi.tech/api/people/20/',
            ],
        ]);

        $this->assertEquals(['10', '20'], $film->characterIds());
    }

    public function test_handles_empty_characters_array(): void
    {
        $film = $this->makeFilm(['characters' => []]);

        $this->assertEquals([], $film->characterIds());
    }

    public function test_to_search_result_contains_only_uid_and_title(): void
    {
        $film = $this->makeFilm();
        $result = $film->toSearchResult();

        $this->assertArrayHasKey('uid', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertCount(2, $result);
        $this->assertEquals('1', $result['uid']);
        $this->assertEquals('A New Hope', $result['title']);
    }

    public function test_to_details_contains_all_relevant_fields(): void
    {
        $film = $this->makeFilm();
        $details = $film->toDetails();

        $this->assertEquals('1', $details['uid']);
        $this->assertEquals('A New Hope', $details['title']);
        $this->assertEquals('It is a period of civil war...', $details['opening_crawl']);
        $this->assertEquals(['1', '2'], $details['character_ids']);
    }

    public function test_handles_missing_properties_gracefully(): void
    {
        $film = Film::fromSwapiProperties('99', []);

        $this->assertEquals('99', $film->uid());
        $this->assertEquals('Unknown', $film->title());
        $this->assertEquals([], $film->characterIds());

        $details = $film->toDetails();
        $this->assertEquals('', $details['opening_crawl']);
    }
}
