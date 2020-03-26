<?php

namespace App\Controller;

use App\Entity\Employee;
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

class ApiEmployeeController extends AbstractController
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
    * @Route("/api/employees", name="api_employee")
    */
    public function index()
    {

        $employees = $this->getDoctrine()->getRepository(Employee::class)->findAll();

        $data = $this->serializer->normalize($employees, null, ['groups' => 'all_employees']);

        $jsonContent = $this->serializer->serialize($data, 'json');

        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
    * @Route("/api/employees", name="api_employee", methods={"POST"})
    */
    public function create(Request $request) {

        $employee = new Employee;

        $employee->setFirstname($request->request->get('firstname'));
        $employee->setLastname( $request->request->get('lastname') );

        // Convertir la date rentrée en string en DateTime
        $date = new \DateTime($request->request->get('employement_date'));

        $employee->setEmployementDate($date);

        // Recupérer les jobs 
        $job = $this->getDoctrine()->getRepository(Job::class)->find( $request->request->get('job_id') );

        $employee->setJob($job);

        /**
         * On enregistre en base de données
         */
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($employee);
        $manager->flush();

        /**
         * On retourne un code 201 (created)
         */
        return new Response(null, 201);
    }

    /**
    * @Route("api/employees/{employee}/edit", name="api_employee_patch", methods={"POST"})
    */
    public function update(Request $request, Employee $employee) {


        if ( !empty($request->request->get('firstname')) ) {
            $employee->setFirstname( $request->request->get('firstname') );
        }

        if ( !empty($request->request->get('lastname')) ) {
            $employee->setLastname( $request->request->get('lastname') );
        }

        if ( !empty($request->request->get('job_id')) ) {
            $employee->setJob( $this->getDoctrine()->getRepository(Job::class)->find( $request->request->get('job_id') ) );
        }

        if ( !empty($request->request->get('employement_date')) ) {
            $date = new \DateTime($request->request->get('employement_date'));
            $employee->setEmployementDate($date);
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->flush();

        return new Response(null, 202);
    }
}
