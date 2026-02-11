<?php

declare(strict_types=1);

namespace Tests\Unit\UseCases;

use App\Application\UseCases\FilmUseCases;
use App\Domain\Entities\Film;
use App\Domain\Entities\Person;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\ExternalServiceException;
use App\Domain\Repositories\FilmRepositoryInterface;
use App\Domain\Repositories\PersonRepositoryInterface;
use App\Domain\ValueObjects\SearchQuery;
use App\Infrastructure\Logging\QueryLogger;
use Tests\TestCase;

class FilmUseCasesTest extends TestCase
{
    private function createUseCases(
        FilmRepositoryInterface $filmRepo,
        ?PersonRepositoryInterface $personRepo = null,
        ?QueryLogger $logger = null,
    ): FilmUseCases {
        $personRepo ??= $this->createMock(PersonRepositoryInterface::class);
        $logger ??= $this->createMock(QueryLogger::class);
        $logger->method('wasLastRequestCached')->willReturn(false);

        return new FilmUseCases($filmRepo, $personRepo, $logger);
    }

    // ---------------------------------------------------------------
    //  searchByTitle
    // ---------------------------------------------------------------

    public function test_search_by_title_returns_results(): void
    {
        $film = Film::fromSwapiProperties('1', [
            'title' => 'A New Hope',
            'opening_crawl' => 'It is a period of civil war...',
            'characters' => ['https://www.swapi.tech/api/people/1'],
        ]);

        $repo = $this->createMock(FilmRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('searchByTitle')
            ->willReturn([$film]);

        $useCases = $this->createUseCases($repo);
        $results = $useCases->searchByTitle(new SearchQuery('hope'));

        $this->assertCount(1, $results);
        $this->assertEquals('A New Hope', $results[0]['title']);
        $this->assertEquals('1', $results[0]['uid']);
    }

    public function test_search_by_title_returns_empty_when_no_matches(): void
    {
        $repo = $this->createMock(FilmRepositoryInterface::class);
        $repo->method('searchByTitle')->willReturn([]);

        $useCases = $this->createUseCases($repo);
        $results = $useCases->searchByTitle(new SearchQuery('zzzzz'));

        $this->assertCount(0, $results);
    }

    public function test_search_by_title_logs_query_on_success(): void
    {
        $repo = $this->createMock(FilmRepositoryInterface::class);
        $repo->method('searchByTitle')->willReturn([]);

        $logger = $this->createMock(QueryLogger::class);
        $logger->method('wasLastRequestCached')->willReturn(false);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'films',
                'test',
                $this->anything(),
                0,
                false,
                false,
            );

        $useCases = new FilmUseCases(
            $repo,
            $this->createMock(PersonRepositoryInterface::class),
            $logger,
        );
        $useCases->searchByTitle(new SearchQuery('test'));
    }

    public function test_search_by_title_logs_query_on_error_and_rethrows(): void
    {
        $repo = $this->createMock(FilmRepositoryInterface::class);
        $repo->method('searchByTitle')
            ->willThrowException(ExternalServiceException::swapiUnavailable('timeout'));

        $logger = $this->createMock(QueryLogger::class);
        $logger->method('wasLastRequestCached')->willReturn(false);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                'films',
                'test',
                $this->anything(),
                0,
                false,
                true,
            );

        $useCases = new FilmUseCases(
            $repo,
            $this->createMock(PersonRepositoryInterface::class),
            $logger,
        );

        $this->expectException(ExternalServiceException::class);
        $useCases->searchByTitle(new SearchQuery('test'));
    }

    // ---------------------------------------------------------------
    //  getDetails
    // ---------------------------------------------------------------

    public function test_get_details_returns_film_with_resolved_characters(): void
    {
        $film = Film::fromSwapiProperties('1', [
            'title' => 'A New Hope',
            'opening_crawl' => 'It is a period of civil war...',
            'characters' => [
                'https://www.swapi.tech/api/people/1',
                'https://www.swapi.tech/api/people/2',
            ],
        ]);

        $person1 = Person::fromSwapiProperties('1', ['name' => 'Luke Skywalker', 'birth_year' => '19BBY', 'gender' => 'Male', 'eye_color' => 'Blue', 'hair_color' => 'Blond', 'height' => '172', 'mass' => '77', 'films' => []]);
        $person2 = Person::fromSwapiProperties('2', ['name' => 'C-3PO', 'birth_year' => '112BBY', 'gender' => 'n/a', 'eye_color' => 'yellow', 'hair_color' => 'n/a', 'height' => '167', 'mass' => '75', 'films' => []]);

        $filmRepo = $this->createMock(FilmRepositoryInterface::class);
        $filmRepo->method('findById')->willReturn($film);

        $personRepo = $this->createMock(PersonRepositoryInterface::class);
        $personRepo->method('findById')
            ->willReturnCallback(fn (string $id) => match ($id) {
                '1' => $person1,
                '2' => $person2,
                default => throw EntityNotFoundException::person($id),
            });

        $useCases = $this->createUseCases($filmRepo, $personRepo);
        $result = $useCases->getDetails('1');

        $this->assertEquals('A New Hope', $result['title']);
        $this->assertEquals('It is a period of civil war...', $result['opening_crawl']);
        $this->assertCount(2, $result['characters']);
        $this->assertEquals('Luke Skywalker', $result['characters'][0]['name']);
        $this->assertEquals('C-3PO', $result['characters'][1]['name']);
        $this->assertArrayNotHasKey('character_ids', $result);
    }

    public function test_get_details_throws_when_film_not_found(): void
    {
        $filmRepo = $this->createMock(FilmRepositoryInterface::class);
        $filmRepo->method('findById')
            ->willThrowException(EntityNotFoundException::film('999'));

        $useCases = $this->createUseCases($filmRepo);

        $this->expectException(EntityNotFoundException::class);
        $useCases->getDetails('999');
    }

    public function test_get_details_gracefully_handles_unresolvable_character(): void
    {
        $film = Film::fromSwapiProperties('1', [
            'title' => 'Test Film',
            'opening_crawl' => 'Test',
            'characters' => ['https://www.swapi.tech/api/people/999'],
        ]);

        $filmRepo = $this->createMock(FilmRepositoryInterface::class);
        $filmRepo->method('findById')->willReturn($film);

        $personRepo = $this->createMock(PersonRepositoryInterface::class);
        $personRepo->method('findById')
            ->willThrowException(EntityNotFoundException::person('999'));

        $useCases = $this->createUseCases($filmRepo, $personRepo);
        $result = $useCases->getDetails('1');

        $this->assertCount(1, $result['characters']);
        $this->assertEquals('Character #999', $result['characters'][0]['name']);
    }
}
