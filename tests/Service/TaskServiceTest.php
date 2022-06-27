<?php
/**
 * Task service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\TaskService;
use App\Service\TaskServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TaskServiceTest.
 */
class TaskServiceTest extends KernelTestCase
{
    /**
     * Task repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Task service.
     */
    private ?TaskServiceInterface $taskService;

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
        $this->taskService = $container->get(TaskService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $expectedTask = new Task();
        $expectedTask->setTitle('Test Task');
        $expectedTask->setCategory($category);

        // when
        $this->taskService->save($expectedTask);

        // then
        $expectedTaskId = $expectedTask->getId();
        $resultTask = $this->entityManager->createQueryBuilder()
            ->select('task')
            ->from(Task::class, 'task')
            ->where('task.id = :id')
            ->setParameter(':id', $expectedTaskId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedTask, $resultTask);
    }

    /**
     * Test delete.
     *
     * @throws OptimisticLockException|ORMException
     */
    public function testDelete(): void
    {
        // given
        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $taskToDelete = new Task();
        $taskToDelete->setTitle('Test task');
        $taskToDelete->setCategory($category);
        $taskToDelete->setCreatedAt((\DateTimeImmutable::createFromFormat('Y-m-d', "2021-05-09")));
        $taskToDelete->setUpdatedAt((\DateTimeImmutable::createFromFormat('Y-m-d', "2021-05-09")));
        $this->entityManager->persist($taskToDelete);
        $this->entityManager->flush();
        $deletedTaskId = $taskToDelete->getId();

        // when
        $this->taskService->delete($taskToDelete);

        // then
        $resultTask = $this->entityManager->createQueryBuilder()
            ->select('task')
            ->from(Task::class, 'task')
            ->where('task.id = :id')
            ->setParameter(':id', $deletedTaskId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultTask);
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
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $task = new Task();
            $task->setTitle('Test task');
            $task->setCategory($category);
            $task->setCreatedAt((\DateTimeImmutable::createFromFormat('Y-m-d', "2021-05-09")));
            $task->setUpdatedAt((\DateTimeImmutable::createFromFormat('Y-m-d', "2021-05-09")));
            $this->taskService->save($task);

            ++$counter;
        }

        $filters = array(
            'category_id' => $category->getId()
        );

        // when
        $result = $this->taskService->getPaginatedList($page, $filters);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Test prepare filters.
     */
    public function testPrepareFilters(): void
    {
        // given
        $category = new Category();
        $category->setTitle('Test Category');
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        $categoryId = $category->getId();

        $filters = array(
            'category_id' => $categoryId,
        );

        // when
        $result = $this->taskService->prepareFilters($filters);

        // then
        $this->assertEquals($result, array('category' => $category));
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     */
    private function createUser(array $roles): User
    {
        $this->removeUser('user@example.com');
        $passwordEncoder = self::$container->get('security.password_encoder');
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = self::$container->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }


    private function removeUser(string $email): void
    {

        $userRepository = static::getContainer()->get(UserRepository::class);
        $entity = $userRepository->findOneBy(array('email' => $email));


        if ($entity !== null) {
            $userRepository->remove($entity);
        }
    }
}