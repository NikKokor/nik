<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Books;
use App\Entity\Logbook;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use MongoDB\Driver\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/book", name="book_api")
 */
class BookController extends ApiController
{
    /**
     * @Route("/add", name="book_add", methods={"POST"})
     */
    public function addBook(Request $request) : JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);
            $entityManager = $this->getDoctrine()->getManager();
            $book = new Books();
            $book->setName($request->get('name'));
            $book->setAuthor($request->get('author'));
            $book->setDate($request->get('date'));

            $entityManager->persist($book);
            $entityManager->flush();

            $data = [
                'status' => Response::HTTP_OK,
                'success' => "Book added successfully",
            ];
            return $this->response($data,[]);
        }
        catch (\Exception $e) {
            $data = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => "Data no valid",
            ];
            return $this->response($data,[Response::HTTP_UNPROCESSABLE_ENTITY]);
        }
    }

    /**
     * @Route("/get/{id}", name="book_get", methods={"GET"})
     */
    public function getBookByID(BookRepository $bookRepository, $id): JsonResponse
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "User not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $bookData = array(
            'id' => $book->getId(),
            'name' => $book->getName(),
            'author' => $book->getAuthor(),
            'date' => $book->getDate()
        );

        return $this->response($bookData,[]);
    }

    /**
     * @Route("/get_all", name="book_get_all", methods={"GET"})
     */
    public function getBooks(BookRepository $bookRepository): JsonResponse
    {
        $books = $bookRepository->findAll();

        $arrayBooks = [];
        foreach ($books as $book){
            $obj = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'author' => $book->getAuthor(),
                'date' => $book->getDate()
            ];
            $arrayBooks[] = $obj;
        }

        if (!$arrayBooks) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Books not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        return $this->response($arrayBooks, []);
    }

    /**
     * @Route("/{id}", name="book_delete", methods={"DELETE"})
     */
    public function deleteUser(BookRepository $bookRepository, $id): JsonResponse
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'errors' => "Book not found",
            ];
            return $this->response($data, [Response::HTTP_NOT_FOUND]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();
        $data = [
            'status' => Response::HTTP_OK,
            'errors' => "Book deleted successfully",
        ];
        return $this->response($data,[]);
    }
}