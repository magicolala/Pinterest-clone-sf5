<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @param PinRepository $repo
     * @return Response
     */
    public function index(PinRepository $repo): Response
    {
        return $this->render('pins/index.html.twig', ['pins' => $repo->findBy([], ['createdAt' => 'DESC'])]);
    }

    /**
     * @Route("/pins/create", name="app_pins_create", methods={"GET", "POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $pin = new Pin();

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid('', true) . '.' . $imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $pin->setImageFilename($newFilename);
            }

            $em->persist($pin);
            $em->flush();

            return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_show")
     * @param Pin $pin
     * @return Response
     */
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }

    /**
     * @Route("/pins/{id<[0-9]+>}/edit", name="app_pins_edit", methods={"GET", "PUT"})
     * @param Request $request
     * @param Pin $pin
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig', [
            'form' => $form->createView(),
            'pin' => $pin
        ]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}/delete", name="app_pins_delete", methods={"DELETE"})
     * @param Request $request
     * @param Pin $pin
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('pin_deletion_' . $pin->getId(), $request->request->get('csrf_token'))) {
            $em->remove($pin);
            $em->flush();
        }

        return $this->redirectToRoute('app_home');
    }
}
