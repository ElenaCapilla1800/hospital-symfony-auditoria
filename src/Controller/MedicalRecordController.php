<?php

namespace App\Controller;

use App\Entity\MedicalRecord;
use App\Form\MedicalRecordType;
use App\Repository\MedicalRecordRepository;
use App\Service\AuditService; // Inyectamos tu servicio de auditoría
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * CONTROLADOR DE HISTORIALES MÉDICOS
 * * Acceso restringido a ROLE_DOCTOR.
 * Maneja el CRUD web y la auditoría de cada acción.
 */
#[Route('/medical/record')]
#[IsGranted('ROLE_DOCTOR')]
final class MedicalRecordController extends AbstractController
{
    /**
     * 1. LISTADO DE HISTORIALES (Filtrado por Seguridad)
     * * Para cumplir con la privacidad, un médico solo debe ver sus propios registros
     * en la tabla, a menos que sea ADMINISTRADOR.
     */
    #[Route(name: 'app_medical_record_index', methods: ['GET'])]
    public function index(MedicalRecordRepository $medicalRecordRepository): Response
    {
        // Si el usuario es ADMIN, ve todo. Si es médico, solo lo suyo.
        if ($this->isGranted('ROLE_ADMIN')) {
            $records = $medicalRecordRepository->findAll();
        } else {
            // Filtramos en la consulta para que no aparezcan registros ajenos
            $records = $medicalRecordRepository->findBy(['doctor' => $this->getUser()]);
        }

        return $this->render('medical_record/index.html.twig', [
            'medical_records' => $records,
        ]);
    }

    /**
     * 2. CREACIÓN DE REGISTROS
     * * Registra un nuevo historial y audita quién lo creó.
     */
    #[Route('/new', name: 'app_medical_record_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $medicalRecord = new MedicalRecord();

        // Seteamos el doctor actual como dueño del registro automáticamente
        $medicalRecord->setDoctor($this->getUser());

        $form = $this->createForm(MedicalRecordType::class, $medicalRecord);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medicalRecord);
            $entityManager->flush();

            // PUNTO 17: Auditoría de creación exitosa (granted = true)
            // Argumentos: (Usuario, Objeto Record, Acción, Éxito)
            $auditService->logAccess($this->getUser(), $medicalRecord, 'CREATE', true);

            $this->addFlash('success', 'Historial médico creado correctamente.');
            return $this->redirectToRoute('app_medical_record_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medical_record/new.html.twig', [
            'medical_record' => $medicalRecord,
            'form' => $form,
        ]);
    }

    /**
     * 3. VER DETALLES
     * * Usa el Voter para denegar acceso si el registro no pertenece al médico.
     */
    #[Route('/{id}', name: 'app_medical_record_show', methods: ['GET'])]
    public function show(MedicalRecord $medicalRecord, AuditService $auditService): Response
    {
        // El Voter MedicalRecordVoter entrará aquí y registrará el log si es denegado
        $this->denyAccessUnlessGranted('RECORD_VIEW', $medicalRecord);

        // Si ha pasado la seguridad, auditamos el acceso de visualización exitoso
        $auditService->logAccess($this->getUser(), $medicalRecord, 'VIEW', true);

        return $this->render('medical_record/show.html.twig', [
            'medical_record' => $medicalRecord,
        ]);
    }

    /**
     * 4. EDICIÓN
     * * Solo permite editar si el Voter confirma que el usuario es el propietario.
     */
    #[Route('/{id}/edit', name: 'app_medical_record_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MedicalRecord $medicalRecord, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        // Seguridad por Voter
        $this->denyAccessUnlessGranted('RECORD_EDIT', $medicalRecord);

        $form = $this->createForm(MedicalRecordType::class, $medicalRecord);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // PUNTO 17: Auditoría de edición
            $auditService->logAccess($this->getUser(), $medicalRecord, 'EDIT', true);

            $this->addFlash('success', 'Historial actualizado con éxito.');
            return $this->redirectToRoute('app_medical_record_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medical_record/edit.html.twig', [
            'medical_record' => $medicalRecord,
            'form' => $form,
        ]);
    }

    /**
     * 5. ELIMINACIÓN
     * * Borra el registro físicamente. El log se guarda ANTES para no perder la referencia.
     */
    #[Route('/{id}', name: 'app_medical_record_delete', methods: ['POST'])]
    public function delete(Request $request, MedicalRecord $medicalRecord, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        // Solo el dueño puede borrar
        $this->denyAccessUnlessGranted('RECORD_EDIT', $medicalRecord);

        if ($this->isCsrfTokenValid('delete' . $medicalRecord->getId(), $request->getPayload()->getString('_token'))) {

            // Registramos el log de borrado ANTES de que el objeto desaparezca de la base de datos
            $auditService->logAccess($this->getUser(), $medicalRecord, 'DELETE', true);

            $entityManager->remove($medicalRecord);
            $entityManager->flush();

            $this->addFlash('success', 'El historial ha sido eliminado.');
        }

        return $this->redirectToRoute('app_medical_record_index', [], Response::HTTP_SEE_OTHER);
    }
}