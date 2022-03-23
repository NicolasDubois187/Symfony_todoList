<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\PictureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PicturesController extends AbstractController
{
    #[Route ('/pictures', name: 'pictures', methods: ['GET'])]
    public function pictures(PictureRepository $pictureRepository): Response
    {
        $pictures = $pictureRepository->findBy([], ['type' => 'ASC']);

        return $this->render('pictures/pictures.html.twig', [
            'pictures' => $pictures
        ]);
    }
    #[Route('/pictureByNameAndType', name: 'pictureByNameAndType', methods: ['GET'])]
        public function pictureByNameAndType (Request $request, PictureRepository $pictureRepository)
    {
        $name =$request->query->get('name');
        $description =$request->query->get('description');
        $pictures = $pictureRepository->findBy(['name' => $name, 'description' => $description]);

        return $this->render('pictures/pictures.html.twig', [
            'pictures' => $pictures,
        ]);
    }

    #[Route ('/addPicture', name: 'addPicture', methods: ['GET', 'POST'])]
    public function addPicture(Request $request, PictureRepository $pictureRepository, SluggerInterface $slugger ): Response
    {
        $picture = new Picture();
        $now = new \DateTime('now');
        $form = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mediaFile = $form->get('media')->getData();
            if($mediaFile) {
                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename. '-' .uniqid().'.'.$mediaFile->guessExtension();
                $mediaFile->move(
                    $this->getParameter('pathUpload_directory'),
                );
                $picture->setMediaFilename($newFilename);
            }

            $picture->setDate($now);
            $pictureRepository->add($picture);
            dd($picture);
            return $this->redirectToRoute('pictures');
        }

        return $this->render('pictures/addPicture.html.twig', ['pictureForm' => $form->createView()]);

    }

}