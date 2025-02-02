<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
    }

    #[Route('/admin/users', name: 'users_list')]
    public function index(): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $this->userRepository->findAll()]);
    }

    #[Route("/admin/users/create", name:"user_create")]
    public function createAction(Request $request)
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $plaintextPassword = $user->getPassword();
            $role = $user->getRoleSelection();
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPassword($hashedPassword);
            $user->setRoles([$role]);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }


    #[Route("/admin/users/{id}/edit", name:"user_edit")]
    public function editAction($id, Request $request)
    {
        $user = $this->userRepository->find($id);
        $emailCurrentUser = $user->getEmail();
        $users = $this->userRepository->findAll();

        $currentRole = $user->getRoles();
        $user->setRoleSelection($currentRole[0]);

        $form = $this->createForm(EditUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataForm = $form->getData();
            $updateEmailForm = $dataForm->getEmail();
    
            //create array with all emails
            $arrayEmails =[];
            foreach ($users as $itemUser){
                $existingEmail = $itemUser->getEmail();
                array_push($arrayEmails, $existingEmail);
            }

            //Vérification si l'émail du formulaire est déjà existant dans le tableau emails existant
            if(in_array($updateEmailForm,$arrayEmails)){
                $indexUpdateEmail = array_search($updateEmailForm ,$arrayEmails);

                //suppresion de l'email formulaire déjà existant dans la liste
                unset($arrayEmails[$indexUpdateEmail]);
            }

            //si l'email du formulaire est identique à l'existant et que celui-ci n'est pas présent dans la liste emails
            //ou 
            //si l'email du formulaire n'est pas identique à l'existant et que celui-ci n'est pas présent dans la liste emails

            if (
                ($updateEmailForm  === $emailCurrentUser &&
                in_array($updateEmailForm, $arrayEmails) === false) ||
                
                ($updateEmailForm !== $emailCurrentUser &&
                in_array($updateEmailForm, $arrayEmails) === false)
            ) {
                $role = $dataForm->getRoleSelection();
                $user->setEmail($updateEmailForm);
                $user->setRoles([$role]);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash('success', "L'utilisateur a bien été modifié");
                return $this->redirectToRoute('users_list');

            } else {
                $this->addFlash('danger','test');
                // $messageError = 'L\'adresse email existe déjà';
                return $this->render('user/edit.html.twig', [
                        'form' => $form->createView(), 
                        'user' => $user, 
                        'error' => true,]);
            }

        } 
        
        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user, 'error' => false]);

    }



}
