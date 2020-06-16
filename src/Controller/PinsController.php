<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @param PinRepository $repo
     * @return Response
     */
    public function index(PinRepository $repo): Response
    {
        return $this->render('pins/index.html.twig', ['pins' => $repo->findAll()]);
    }

    /**
     * @Route("/pins/create", name="app_pins_create", methods={"GET"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $pin = new Pin();

        $form = $this->createFormBuilder($pin)
            ->add('title', TextType::class, [
                'attr' => ['autofocus' => true]
            ])
            ->add('description', TextareaType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pin);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_show")
     * @param PinRepository $repo
     * @param $id
     * @return Response
     */
    public function show(PinRepository $repo, $id): Response
    {
        $pin = $repo->find($id);

        if (!$pin) {
            throw $this->createNotFoundException('Pin #' . $id . ' not found.');
        }

        return $this->render('pins/show.html.twig', compact('pin'));
    }
}
