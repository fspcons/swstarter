<?php

declare(strict_types=1);

namespace Tests\Unit\UseCases;

use App\Application\UseCases\PeopleUseCases;
use App\Domain\Entities\Film;
use App\Domain\Entities\Person;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ExternalServiceException;
use App\Domain\Repositories\FilmRepositoryInterface;
use App\Domain\Repositories\PersonRepositoryInterface;
use App\Domain\ValueObjects\SearchQuery;
use App\Infrastructure\Logging\QueryLogger;
use Tests\TestCase;

class PeopleUseCasesTest extends TestCase
{
    private function createUseCases(
        PersonRepositoryInterface $personRepo,
        ?FilmRepositoryInterface $filmRepo = null,
        ?QueryLogger $logger = null,
    ): PeopleUseCases {
        $filmRepo ??= $this->createMock(FilmRepositoryInterface::class);
        $logger ??= $this->createMock(QueryLogger::class);
        $logger->method('wasLastRequestCached')->willReturn(false);

        return new PeopleUseCases($personRepo, $filmRepo, $logger);
    }

    // ---------------------------------------------------------------
    //  searchByName
    // ---------------------------------------------------------------

    public function test_search_by_name_returns_results(): void
    {
        $person = Person::fromSwapiProperties('9', [
            'name' => 'Biggs Darklighter',
            'birth_year' => '24BBY',
            'gender' => 'male',
            'eye_color' => 'brown',
            'hair_color' => 'black',
            'height' => '183',
            'mass' => '84',
            'films' => ['https://www.swapi.tech/api/films/1'],
        ]);

        $repo = $this->createMock(PersonRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('searchByName')
            ->willReturn([$person]);

        $useCases = $this->createUseCases($repo);
        $results = $useCases->searchByName(new SearchQuery('bi'));

        $this->assertCount(1, $results);
        $this->assertEquals('Biggs Darklighter', $results[0]['name']);
        $this->assertEquals('9', $results[0]['uid']);
    }

    public function test_search_by_name_returns_empty_when_no_matches(): void
    {
        $repo = $this->createMock(PersonRepositoryInterface::class);
        $repo->method('searchByName')->willReturn([]);

        $useCases = $this->createUseCases($repo);
        $results = $useCases->searchByName(new SearchQuery('zzzzz'));

        $this->assertCount(0, $results);
    }

    public function test_search_by_name_returns_multiple_results(): void
    {
        $people = [
            Person::fromSwapiProperties('1', ['name' => 'Luke', 'birth_year' => '19BBY', 'gender' => 'Male', 'eye_color' => 'Blue', 'hair_color' => 'Blond', 'height' => '172', 'mass' => '77', 'films' => []]),
            Person::fromSwapiProperties('2', ['name' => 'Leia', 'birth_year' => '19BBY', 'gender' => 'Female', 'eye_color' => 'Brown', 'hair_color' => 'Brown', 'height' => '150', 'mass' => '49', 'films' => []]),
        ];

        $repo = $this->createMock(PersonRepositoryInterface::class);
        $repo->method('searchByName')->willReturn($people);

        $useCases = $this->createUseCases($repo);
        $results = $useCases->searchByName(new SearchQuery('l'));

        $this->assertCount(2, $results);
        $this->assertEquals('Luke', $results[0]['name']);
        $this->assertEquals('Leia', $results[1]['name']);
    }

    public function test_search_by_name_logs_query_on_success(): void
    {
        $repo = $this->createMock(PersonRepositoryInterface::class);
        $repo->method('searchByName')->willReturn([]);

        $logger = $this->createMock(QueryLogger::class);
        $logger->method('wasLastRequestCached')->willReturn(true);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'people',
                'test',
                $this->anything(),
                0,
                true,
                false,
            );

        $useCases = new PeopleUseCases(
            $repo,
            $this->createMock(FilmRepositoryInterface::class),
            $logger,
        );
        $useCases->searchByName(new SearchQuery('test'));
    }

    public function test_search_by_name_logs_query_on_error_and_rethrows(): void
    {
        $repo = $this->createMock(PersonRepositoryInterface::class);
        $repo->method('searchByName')
            ->willThrowException(ExternalServiceException::swapiUnavailable('timeout'));

        $logger = $this->createMock(QueryLogger::class);
        $logger->method('wasLastRequestCached')->willReturn(false);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'people',
                'test',
                $this->anything(),
                0,
                false,
                true,
            );

        $useCases = new PeopleUseCases(
            $repo,
            $this->createMock(FilmRepositoryInterface::class),
            $logger,
        );

        $this->expectException(ExternalServiceException::class);
        $useCases->searchByName(new SearchQuery('test'));
    }

    // ---------------------------------------------------------------
    //  getDetails
    // ---------------------------------------------------------------

    public function test_get_details_returns_person_with_resolved_films(): void
    {
        $person = Person::fromSwapiProperties('1', [
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
            ],
        ]);

        $film1 = Film::fromSwapiProperties('1', ['title' => 'A New Hope', 'opening_crawl' => '', 'characters' => []]);
        $film2 = Film::fromSwapiProperties('2', ['title' => 'The Empire Strikes Back', 'opening_crawl' => '', 'characters' => []]);

        $personRepo = $this->createMock(PersonRepositoryInterface::class);
        $personRepo->method('findById')->willReturn($person);

        $filmRepo = $this->createMock(FilmRepositoryInterface::class);
        $filmRepo->method('findById')
            ->willReturnCallback(fn (string $id) => match ($id) {
                '1' => $film1,
                '2' => $film2,
                default => throw EntityNotFoundException::film($id),
            });

        $useCases = $this->createUseCases($personRepo, $filmRepo);
        $result = $useCases->getDetails('1');

        $this->assertEquals('Luke Skywalker', $result['name']);
        $this->assertCount(2, $result['films']);
        $this->assertEquals('A New Hope', $result['films'][0]['title']);
        $this->assertEquals('The Empire Strikes Back', $result['films'][1]['title']);
        $this->assertArrayNotHasKey('film_ids', $result);
    }

    public function test_get_details_throws_when_person_not_found(): void
    {
        $personRepo = $this->createMock(PersonRepositoryInterface::class);
        $personRepo->method('findById')
            ->willThrowException(EntityNotFoundException::person('999'));

        $useCases = $this->createUseCases($personRepo);

        $this->expectException(EntityNotFoundException::class);
        $useCases->getDetails('999');
    }

    public function test_get_details_gracefully_handles_unresolvable_film(): void
    {
        $person = Person::fromSwapiProperties('1', [
            'name' => 'Test',
            'birth_year' => '19BBY',
            'gender' => 'Male',
            'eye_color' => 'Blue',
            'hair_color' => 'Blond',
            'height' => '172',
            'mass' => '77',
            'films' => ['https://www.swapi.tech/api/films/999'],
        ]);

        $personRepo = $this->createMock(PersonRepositoryInterface::class);
        $personRepo->method('findById')->willReturn($person);

        $filmRepo = $this->createMock(FilmRepositoryInterface::class);
        $filmRepo->method('findById')
            ->willThrowException(EntityNotFoundException::film('999'));

        $useCases = $this->createUseCases($personRepo, $filmRepo);
        $result = $useCases->getDetails('1');

        $this->assertCount(1, $result['films']);
        $this->assertEquals('Film #999', $result['films'][0]['title']);
    }
}
