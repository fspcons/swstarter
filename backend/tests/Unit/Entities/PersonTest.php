<?php

declare(strict_types=1);

namespace Tests\Unit\Entities;

use App\Domain\Entities\Person;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    private function makePerson(array $overrides = []): Person
    {
        return Person::fromSwapiProperties('1', array_merge([
            'name' => 'Luke Skywalker',
            'birth_year' => '19BBY',
            'gender' => 'Male',
            'eye_color' => 'Blue',
            'hair_color' => 'Blond',
            'height' => '172',
            'mass' => '77',
            'films' => [
                'https://www.swapi.tech/api/films/1',
                'https://www.swapi.tech/api/films/2',
                'https://www.swapi.tech/api/films/3',
            ],
        ], $overrides));
    }

    public function test_creates_from_swapi_properties(): void
    {
        $person = $this->makePerson();

        $this->assertEquals('1', $person->uid());
        $this->assertEquals('Luke Skywalker', $person->name());
    }

    public function test_extracts_film_ids_from_urls(): void
    {
        $person = $this->makePerson();

        $this->assertEquals(['1', '2', '3'], $person->filmIds());
    }

    public function test_extracts_film_ids_from_urls_with_trailing_slashes(): void
    {
        $person = $this->makePerson([
            'films' => [
                'https://www.swapi.tech/api/films/1/',
                'https://www.swapi.tech/api/films/5/',
            ],
        ]);

        $this->assertEquals(['1', '5'], $person->filmIds());
    }

    public function test_handles_empty_films_array(): void
    {
        $person = $this->makePerson(['films' => []]);

        $this->assertEquals([], $person->filmIds());
    }

    public function test_to_search_result_contains_only_uid_and_name(): void
    {
        $person = $this->makePerson();
        $result = $person->toSearchResult();

        $this->assertArrayHasKey('uid', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertCount(2, $result);
        $this->assertEquals('1', $result['uid']);
        $this->assertEquals('Luke Skywalker', $result['name']);
    }

    public function test_to_details_contains_all_relevant_fields(): void
    {
        $person = $this->makePerson();
        $details = $person->toDetails();

        $this->assertEquals('1', $details['uid']);
        $this->assertEquals('Luke Skywalker', $details['name']);
        $this->assertEquals('19BBY', $details['birth_year']);
        $this->assertEquals('Male', $details['gender']);
        $this->assertEquals('Blue', $details['eye_color']);
        $this->assertEquals('Blond', $details['hair_color']);
        $this->assertEquals('172', $details['height']);
        $this->assertEquals('77', $details['mass']);
        $this->assertEquals(['1', '2', '3'], $details['film_ids']);
    }

    public function test_handles_missing_properties_gracefully(): void
    {
        $person = Person::fromSwapiProperties('99', []);

        $this->assertEquals('99', $person->uid());
        $this->assertEquals('Unknown', $person->name());
        $this->assertEquals([], $person->filmIds());

        $details = $person->toDetails();
        $this->assertEquals('unknown', $details['gender']);
        $this->assertEquals('unknown', $details['birth_year']);
    }
}
