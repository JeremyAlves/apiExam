<?php

namespace App\Controller;

use App\Entity\Job;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiJobController extends AbstractController
{
    public $serializer;

    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer($classMetadataFactory)];

        $this->serializer = new Serializer($normalizers, $encoders);


    }

    /**
    * @Route("/api/jobs", name="api_job", methods={"GET"})
    */
    public function index()
    {

        $employees = $this->getDoctrine()->getRepository(Job::class)->findAll();

        $data = $this->serializer->normalize($employees, null, ['groups' => 'all_jobs']);

        $jsonContent = $this->serializer->serialize($data, 'json');

        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
    * @Route("api/jobs/{job}", name="job_show", methods={"GET"}, requirements={"job"="\d+"})
    */
    public function show(Job $job): Response
    {
        // récuperer les infos d'un job
        $job = $this->getDoctrine()->getRepository(Job::class)->find($job);

        $data = $this->serializer->normalize($job, null, ['groups' => 'all_jobs']);

        $jsonContent = $this->serializer->serialize($data, 'json');

        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
    * @Route("/api/job/add", name="api_job_add", methods={"POST"})
    */
    public function create(Request $request) {

        $job = new Job;

        $job->setTitle($request->request->get('title'));

        /**
         * On enregistre en base de données
         */
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($job);
        $manager->flush();

        /**
         * On retourne un code 201 (created)
         */
        return new Response(null, 201);
    }

    /**
    * @Route("api/jobs/{job}/edit", name="api_job_patch", methods={"POST"})
    */
    public function update(Request $request, Job $job) {


        if ( !empty($request->request->get('title')) ) {
            $job->setTitle( $request->request->get('title') );
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->flush();

        return new Response(null, 202);
    }

    /**
    * @Route("api/jobs/{job}", name="api_job_delete", methods={"DELETE"})
    */
    public function delete(Request $request, Job $job) {

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($job);

        $manager->flush();

        return new Response(null, 200);
    }
}

