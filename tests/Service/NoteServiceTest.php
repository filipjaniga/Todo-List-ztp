<?php
/**
 * Note service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Note;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\NoteService;
use App\Service\NoteServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class NoteServiceTest.
 */
class NoteServiceTest extends KernelTestCase
{
    /**
     * Note repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Note service.
     */
    private ?NoteServiceInterface $noteService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->noteService = $container->get(NoteService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given

        $expectedNote = new Note();
        $expectedNote->setTitle('Test Note');
        $expectedNote->setContent('Test Note Content');

        // when
        $this->noteService->save($expectedNote);

        // then
        $expectedNoteId = $expectedNote->getId();
        $resultNote = $this->entityManager->createQueryBuilder()
            ->select('note')
            ->from(Note::class, 'note')
            ->where('note.id = :id')
            ->setParameter(':id', $expectedNoteId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedNote, $resultNote);
    }

    /**
     * Test delete.
     *
     * @throws OptimisticLockException|ORMException
     */
    public function testDelete(): void
    {
        // given
        $noteToDelete = new Note();
        $noteToDelete->setTitle('Test note');
        $noteToDelete->setCreatedAt((\DateTimeImmutable::createFromFormat('Y-m-d', "2021-05-09")));
        $noteToDelete->setUpdatedAt((\DateTimeImmutable::createFromFormat('Y-m-d', "2021-05-09")));
        $this->entityManager->persist($noteToDelete);
        $this->entityManager->flush();
        $deletedNoteId = $noteToDelete->getId();

        // when
        $this->noteService->delete($noteToDelete);

        // then
        $resultNote = $this->entityManager->createQueryBuilder()
            ->select('note')
            ->from(Note::class, 'note')
            ->where('note.id = :id')
            ->setParameter(':id', $deletedNoteId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultNote);
    }


    /**
     * Test pagination empty list.
     */
    public function testGetPaginatedList(): void
    {
        // given
        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $page = 1;
        $dataSetSize = 2;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $note = new Note();
            $note->setTitle('Test note');
            $note->setContent('Test Note content');
            $this->noteService->save($note);

            ++$counter;
        }

        $filters = ['category_id' => $category->getId()];

        // when
        $result = $this->noteService->getPaginatedList($page, $filters);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }



}