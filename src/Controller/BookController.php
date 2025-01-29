<?php

namespace App\Controller;

use App\Service\BookGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends AbstractController
{
    #[Route('/', name: 'app_books')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig');
    }

    #[Route('/api/books', name: 'get_books', methods: ['GET'])]
    public function getBooks(Request $request, BookGenerator $bookGenerator): JsonResponse
    {
        $seed = (int) $request->query->get('seed', 42);
        $language = $request->query->get('language', 'en');
        $page = (int) $request->query->get('page', 1);
        $likes = (float) $request->query->get('likes', 5);
        $reviews = (float) $request->query->get('reviews', 4.7);

        $books = $bookGenerator->generateBooks($page, $seed, $language, $likes, $reviews);
        return $this->json($books);
    }

    #[Route('/api/export', name: 'export_books', methods: ['GET'])]
    public function exportBooks(Request $request, BookGenerator $bookGenerator): StreamedResponse
    {
        $seed = (int) $request->query->get('seed', 42);
        $language = $request->query->get('language', 'en');
        $pages = (int) $request->query->get('pages', 1);
        $likes = (float) $request->query->get('likes', 5);
        $reviews = (float) $request->query->get('reviews', 4.7);

        $response = new StreamedResponse(function () use ($bookGenerator, $pages, $seed, $language, $likes, $reviews) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Index', 'ISBN', 'Title', 'Author', 'Publisher', 'Likes']);

            for ($page = 1; $page <= $pages; $page++) {
                $books = $bookGenerator->generateBooks($page, $seed, $language, $likes, $reviews);
                foreach ($books as $book) {
                    fputcsv($handle, [$book['index'], $book['isbn'], $book['title'], $book['author'], $book['publisher'], $book['likes']]);
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="books.csv"');

        return $response;
    }
}
