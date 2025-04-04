<?php

namespace App\Controller;

use App\Entity\User;
use Twig\TwigFunction;
use App\Entity\Stagiaire;
use App\Form\AddStagiaireFormType;
use App\Form\EditStagiaireFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class MainController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function home(ManagerRegistry $doctrine): Response
    {

        $repository = $doctrine->getRepository(User::class);

        $users = $repository->findAll();

        $countUsers = count($users);

        return $this->render('main/home.html.twig', [
            'countUsers' => $countUsers,
        ]);
    }

    #[Route('/appel/', name: 'appel')]
    #[IsGranted('ROLE_ADMIN')]
    public function appel(ManagerRegistry $doctrine): Response
    {

        $repository = $doctrine->getRepository(Stagiaire::class);

        $users = $repository->findAll();

        foreach ($users as $user) {
            $status = $user->getStatus();
        }

        $countUsers = count($users);

        if ($countUsers == 0) {
            $this->addFlash('error', 'Vous n\'avez pas de stagiaire enregistré.');
            return $this->redirectToRoute('home');
        } else if (!empty($status)){
            $this->addFlash('info', 'Vous avez terminé l\'appel, si vous voulez en faire une nouvelle, réinitialisez là à l\'accueil.');
            return $this->redirectToRoute('stagiaires');
        }

        return $this->render('main/appel.html.twig', [
            'stagiaires' => $users,
        ]);
    }

    #[Route('/reset/', name: 'reset')]
    #[IsGranted('ROLE_ADMIN')]
    public function reset(ManagerRegistry $doctrine): Response
    {

        $repository = $doctrine->getRepository(Stagiaire::class);

        $users = $repository->findAll();

        $countUsers = count($users);

        $entityManager = $doctrine->getManager();

        foreach ($users as $user) {
            $user->setStatus('');
            $entityManager->persist($user);
        }

        $entityManager->flush();

        if ($countUsers == 0) {
            $this->addFlash('error', 'Vous n\'avez pas de stagiaire enregistré.');
            return $this->redirectToRoute('home');
        }

        return $this->render('main/appel.html.twig', [
            'stagiaires' => $users,
        ]);
    }

    #[Route('/mentions-legales/', name: 'mentions_legales')]
    #[IsGranted('ROLE_ADMIN')]
    public function mentionsLegales(): Response
    {
        return $this->render('main/mentions_legales.html.twig');
    }

    #[Route('/ajouter-un-stagiaire/', name: 'add_stagiaire')]
    #[IsGranted('ROLE_ADMIN')]
    public function addstagiaire(Request $request, ManagerRegistry $doctrine): Response
    {

        $stagiaire = new Stagiaire();

        $form = $this->createForm(AddStagiaireFormType::class, $stagiaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doctrine->getManager();

            $em->persist($stagiaire);

            $em->flush();

            $this->addFlash('success', 'Le stagiaire a bien été ajouté !');

            return $this->redirectToRoute('add_stagiaire');
        }

        return $this->render('main/add_stagiaire.html.twig', [
            'add_stagiaire_form' => $form->createView(),
        ]);
    }

    #[Route('/stagiaires/', name: 'stagiaires')]
    #[IsGranted('ROLE_ADMIN')]
    public function stagiaires(ManagerRegistry $doctrine): Response
    {

        $repository = $doctrine->getRepository(Stagiaire::class);

        $users = $repository->findAll();

        return $this->render('main/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/retirer-stagiaire/{id}/', name: 'remove_user', priority: 10)]
    #[ParamConverter('user', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeUser(User $user, ManagerRegistry $doctrine): Response
    {

        $em = $doctrine->getManager();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Le stagiaire a bien été retiré.');

        return $this->redirectToRoute('users');
    }

    #[Route('/modifier-stagiaire/{id}/', name: 'edit_user', priority: 10)]
    #[ParamConverter('stagiaire', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function editStagiaire(Stagiaire $stagiaire, Request $request, ManagerRegistry $doctrine): Response
    {

        $form = $this->createForm(EditStagiaireFormType::class, $stagiaire);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Le stagiaire a bien été modifié !');

            return $this->redirectToRoute('stagiaires');
        }

        return $this->render('main/edit_stagiaire.html.twig', [
            'edit_stagiaire_form' => $form->createView(),
        ]);
    }

    #[Route('/retirer-stagiaire/{id}/', name: 'remove_user', priority: 10)]
    #[ParamConverter('stagiaire', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function removeStagiaire(Stagiaire $stagiaire, Request $request, ManagerRegistry $doctrine): Response
    {

        $em = $doctrine->getManager();
        $em->remove($stagiaire);
        $em->flush();

        $this->addFlash('success', 'Le stagiaire a bien été retiré !');

        return $this->redirectToRoute('stagiaires');
    }

    #[Route('/stagiaire-present/{id}/', name: 'stagiaire_present')]
    #[ParamConverter('stagiaire', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function stagiairePresent(Stagiaire $stagiaire, ManagerRegistry $doctrine): Response
    {

        $entityManager = $doctrine->getManager();

        $stagiaire->setStatus('Présent');

        $entityManager->persist($stagiaire);

        $entityManager->flush();

        $this->addFlash('success', 'Marqué présent!');

        return $this->redirectToRoute('appel');
    }

    #[Route('/stagiaire-absent/{id}/', name: 'stagiaire_absent')]
    #[ParamConverter('stagiaire', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function stagiaireAbsent(Stagiaire $stagiaire, ManagerRegistry $doctrine): Response
    {

        $entityManager = $doctrine->getManager();

        $stagiaire->setStatus('Absent');

        $entityManager->persist($stagiaire);

        $entityManager->flush();

        $this->addFlash('success', 'Marqué absent!');

        return $this->redirectToRoute('appel');
    }

    #[Route('/stagiaire-en-demarche/{id}/', name: 'stagiaire_en_demarche')]
    #[ParamConverter('stagiaire', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function stagiaireEnDemarche(Stagiaire $stagiaire, ManagerRegistry $doctrine): Response
    {

        $entityManager = $doctrine->getManager();

        $stagiaire->setStatus('En démarche');

        $entityManager->persist($stagiaire);

        $entityManager->flush();

        $this->addFlash('success', 'Marqué en démarche!');

        return $this->redirectToRoute('appel');
    }

    #[Route('/stagiaire-en-stage/{id}/', name: 'stagiaire_en_stage')]
    #[ParamConverter('stagiaire', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function stagiaireEnStage(Stagiaire $stagiaire, ManagerRegistry $doctrine): Response
    {

        $entityManager = $doctrine->getManager();

        $stagiaire->setStatus('En stage');

        $entityManager->persist($stagiaire);

        $entityManager->flush();

        $this->addFlash('success', 'Marqué en stage!');

        return $this->redirectToRoute('appel');
    }

    #[Route('/stagiaire-en-foad/{id}/', name: 'stagiaire_en_foad')]
    #[ParamConverter('stagiaire', options: ['mapping' => ['id' => 'id']])]
    #[IsGranted('ROLE_ADMIN')]
    public function stagiaireEnFoad(Stagiaire $stagiaire, ManagerRegistry $doctrine): Response
    {

        $entityManager = $doctrine->getManager();

        $stagiaire->setStatus('FOAD');

        $entityManager->persist($stagiaire);

        $entityManager->flush();

        $this->addFlash('success', 'Marqué en FOAD!');

        return $this->redirectToRoute('appel');
    }

    #[Route('/recherche/', name: 'search')]
    public function search(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        // Récupération de $_GET['page'], 1 si elle n'existe pas
        $requestedPage = $request->query->getInt('page', 1);

        // Vérification que le nombre est positif

        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        // On récupère la recherche de l'utilisateur depuis l'URL ( $_GET['search'] )
        $search = $request->query->get('search', '');

        $em = $doctrine->getManager();

        //Création de la requête de recherche
        $query = $em
            ->createQuery('SELECT s FROM App\Entity\Stagiaire s WHERE s.firstname LIKE :search OR s.lastname LIKE :search OR s.status LIKE :search')
            ->setParameters([
                'search' => '%' . $search . '%'
            ]);

        $articles = $paginator->paginate(
            $query,     // Requête créée juste avant
            $requestedPage,     // Page qu'on souhaite voir
            1000,     // Nombre d'article à afficher par page
        );

        return $this->render('main/search.html.twig', [
            'articles' => $articles,
        ]);
    }

}