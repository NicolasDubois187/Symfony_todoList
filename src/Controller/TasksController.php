<?php
namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TasksController extends AbstractController
{
    #[Route('/tasks', name: 'tasks', methods: ['GET'])]
    public function tasks (TaskRepository $tasksRepository):Response
    {
        $tasks = $tasksRepository->findBy(['done' => true]);
        $tasksDone = $tasksRepository->findBy(['done' => false]);


        return $this->render('tasks/tasks.html.twig', [
            'tasks' => $tasks,
            'tasksDone' => $tasksDone
        ]);
    }

    #[Route('/task/{id}', name: 'task', methods: ['GET'])]
    public function task (TaskRepository $taskRepository, $id)
    {
        $task = $taskRepository->findOneBy(["id" => $id]);
        return $this->render('tasks/task.html.twig', ['task' => $task]);
    }

    #[Route('/taskDelete/{id}', name: 'deleteTask', methods: ['GET'])]
    public function deleteTask (
        TaskRepository $taskRepository,
        $id,
        EntityManagerInterface $entityManager
    )
    {
        $task = $taskRepository->findOneBy(["id" => $id]);
        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('tasks');
    }

    #[Route('/taskStatus/{id}', name: 'changeStatus', methods: ['GET'])]
    public function changeStatus (
        TaskRepository $taskRepository,
        $id,
        EntityManagerInterface $entityManager
    )
    {
        $task = $taskRepository->findOneBy(["id" => $id]);
        if ($task->getDone(true)) {
            $task->setDone(false);
        } else {
            $task->setDone(true);
        }
        $entityManager->persist($task);
        $entityManager->flush();
        return $this->redirectToRoute('tasks');
    }

    #[Route('/taskAdd', name: 'taskAdd', methods: ['GET', 'POST'])]
    function addTask (Request $request, TaskRepository $taskRepository):Response
    {
        $task = new Task();
        $now = date('H:i:s \O\n d/m/Y');
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreation(new \DateTime('now'))
                ->setDone(true);
            $taskRepository->add($task);
            return $this->redirectToRoute('tasks');
        }
        return $this->render('tasks/taskAdd.html.twig', ['taskForm' => $form->createView()]);
    }


    #[Route('updateTask/{id}', name: 'taskUpdate', methods: ['GET', 'POST'])]
    public function taskUpdate($id, TaskRepository $taskRepository, Request $request)
    {
        $task = $taskRepository->findOneBy(["id" => $id]);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->add($task);
            return $this->redirectToRoute('tasks');
        }
        return $this->render('tasks/updateTask.html.twig', [
            'taskForm' => $form->createView(),
            'task' => $task
        ]);
    }
}
