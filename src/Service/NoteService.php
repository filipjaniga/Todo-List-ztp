<?php
/**
 * Note service.
 */

namespace App\Service;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class NoteService.
 */
class NoteService implements NoteServiceInterface
{
    /**
     * Note repository.
     */
    private NoteRepository $noteRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Constructor.
     *
     * @param NoteRepository     $noteRepository Note repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(NoteRepository $noteRepository, PaginatorInterface $paginator)
    {
        $this->noteRepository = $noteRepository;
        $this->paginator = $paginator;
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->noteRepository->queryAll(),
            $page,
            NoteRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Note $note Note entity
     */
    public function save(Note $note): void
    {
        if (null === $note->getId()) {
            $note->setCreatedAt(new \DateTimeImmutable());
        }
        $note->setUpdatedAt(new \DateTimeImmutable());

        $this->noteRepository->save($note);
    }

    /**
     * Delete entity.
     *
     * @param Note $note Note Entity
     */
    public function delete(Note $note): void
    {
        $this->noteRepository->delete($note);
    }
}
