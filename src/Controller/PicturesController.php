<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\MediaRepository;
use App\Repository\PictureRepository;
use App\Repository\TypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PicturesController extends AbstractController
{
    #[Route ('/pictures', name: 'pictures', methods: ['GET'])]
    public function pictures(PictureRepository $pictureRepository, TypeRepository $typeRepository): Response
    {
        $pictures = $pictureRepository->findBy([], ['type' => 'ASC']);
        $types = $typeRepository->findAll();

        return $this->render('pictures/pictures.html.twig', [
            'pictures' => $pictures,
            'types' => $types,

        ]);
    }
    #[Route('/pictureByNameAndType', name: 'pictureByNameAndType', methods: ['GET'])]
        public function pictureByNameAndType (
            Request $request,
            PictureRepository $pictureRepository,
            TypeRepository $typeRepository
             )
    {
        $name =$request->query->get('name');
        $description =$request->query->get('description');
        $type = $request->query->get('type');
        //$pictures = $pictureRepository->findBy(['name' => $name, 'description' => $description], ['date' => 'ASC']);
        $pictures = $pictureRepository->getPictureByNameAndType($name, $description, $type);
        $types = $typeRepository->findAll();
        return $this->render('pictures/pictures.html.twig', [
            'pictures' => $pictures,
            'types' => $types
        ]);
    }

    #[Route ('/addPicture', name: 'addPicture', methods: ['GET', 'POST'])]
    public function addPicture(Request $request, PictureRepository $pictureRepository, SluggerInterface $slugger): Response
    {
        $picture = new Picture();
        $media = new Media();

        $now = new \DateTime('now');
        $form = $this->createForm(PictureType::class, $picture);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mediaFile = $form->get('mediaFilename')->getData();
            if($mediaFile) {
                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename. '-' .uniqid().'.'.$mediaFile->guessExtension();
                $mediaFile->move(
                    $this->getParameter('pathUpload_directory'), $newFilename
                );
                $media->setName($newFilename);
                $picture->setMedia($media);

            }

            $picture->setDate($now);
            $pictureRepository->add($picture);

            return $this->redirectToRoute('pictures');
        }

        return $this->render('pictures/addPicture.html.twig', ['pictureForm' => $form->createView()]);

    }

}