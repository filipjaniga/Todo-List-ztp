<?php
/**
 * Task service.
 */

namespace App\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TaskService.
 */
class TaskService implements TaskServiceInterface
{
    /**
     * Task repository.
     */
    private TaskRepository $taskRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Constructor.
     *
     * @param TaskRepository           $taskRepository  Task repository
     * @param PaginatorInterface       $paginator       Paginator
     * @param CategoryServiceInterface $categoryService Category service
     */
    public function __construct(TaskRepository $taskRepository, PaginatorInterface $paginator, CategoryServiceInterface $categoryService)
    {
        $this->taskRepository = $taskRepository;
        $this->paginator = $paginator;
        $this->categoryService = $categoryService;
    }

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->taskRepository->queryAll($filters),
            $page,
            TaskRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        if (null === $task->getId()) {
            $task->setCreatedAt(new \DateTimeImmutable());
        }
        $task->setUpdatedAt(new \DateTimeImmutable());

        $this->taskRepository->save($task);
    }

    /**
     * Delete entity.
     *
     * @param Task $task Task Entity
     */
    public function delete(Task $task): void
    {
        $this->taskRepository->delete($task);
    }

    /**
     * Prepare filters for the tasks list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     */
    public function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        return $resultFilters;
    }
}
