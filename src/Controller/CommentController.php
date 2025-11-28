<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/comment/add/{articleId}', name: 'comment_add', methods: ['POST'])]
    public function add(
        int $articleId,
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em
    ): Response {

        $article = $articleRepository->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Si l’utilisateur n’est pas connecté, le redirige vers login
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $content = trim($request->request->get('content'));
        if ($content === '') {
            return $this->redirectToRoute('app_events', ['_fragment' => 'article-' . $articleId]);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setUser($user);
        $comment->setArticle($article);
        $comment->setDateComment(new \DateTime());

        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('app_events', ['_fragment' => 'article-' . $articleId]);
    }
}
