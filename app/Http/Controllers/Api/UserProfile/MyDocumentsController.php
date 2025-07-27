<?php

namespace App\Http\Controllers\Api\UserProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\PerfilInversor;
use App\Models\PerfilTrabajador;
use App\Models\PerfilConstructorContratista;
use Illuminate\Support\Facades\Storage;

class MyDocumentsController extends Controller
{
    /**
     * Display a listing of user documents
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $documents = [];

        // Get documents based on user type
        switch ($user->tipo_perfil) {
            case 'Inversor':
                if ($user->perfilInversor) {
                    $documents = $this->getInversorDocuments($user->perfilInversor);
                }
                break;
            case 'Trabajador':
                if ($user->perfilTrabajador) {
                    $documents = $this->getTrabajadorDocuments($user->perfilTrabajador);
                }
                break;
            case 'ConstructorContratista':
                if ($user->perfilConstructorContratista) {
                    $documents = $this->getConstructorDocuments($user->perfilConstructorContratista);
                }
                break;
        }

        return response()->json([
            'data' => $documents
        ], 200);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'tipo_documento' => 'required|string',
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Store file
        $filePath = $request->file('documento')->store('documentos', 'public');
        
        // Update specific profile based on user type
        switch ($user->tipo_perfil) {
            case 'Inversor':
                $this->updateInversorDocument($user->perfilInversor, $request->tipo_documento, $filePath);
                break;
            case 'Trabajador':
                $this->updateTrabajadorDocument($user->perfilTrabajador, $request->tipo_documento, $filePath);
                break;
            case 'ConstructorContratista':
                $this->updateConstructorDocument($user->perfilConstructorContratista, $request->tipo_documento, $filePath);
                break;
        }

        return response()->json([
            'message' => 'Documento subido exitosamente',
            'file_url' => Storage::url($filePath)
        ], 201);
    }

    /**
     * Display the specified document
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        // This would typically return document details
        // For now, we'll return a generic response
        return response()->json([
            'message' => 'Documento encontrado',
            'document_id' => $id
        ], 200);
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'descripcion' => 'nullable|string|max:500',
        ]);

        // Update document logic here
        $filePath = $request->file('documento')->store('documentos', 'public');

        return response()->json([
            'message' => 'Documento actualizado exitosamente',
            'file_url' => Storage::url($filePath)
        ], 200);
    }

    /**
     * Remove the specified document
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        // Delete document logic here
        // This would typically delete the file and update the database

        return response()->json([
            'message' => 'Documento eliminado exitosamente'
        ], 200);
    }

    /**
     * Get investor documents
     */
    private function getInversorDocuments($perfilInversor)
    {
        $documents = [];

        if ($perfilInversor->url_id_oficial) {
            $documents[] = [
                'tipo' => 'ID Oficial',
                'url' => $perfilInversor->url_id_oficial,
                'estado' => 'Completado'
            ];
        }

        if ($perfilInversor->url_prueba_fondos) {
            $documents[] = [
                'tipo' => 'Prueba de Fondos',
                'url' => $perfilInversor->url_prueba_fondos,
                'estado' => 'Completado'
            ];
        }

        if ($perfilInversor->url_formulario_tributario) {
            $documents[] = [
                'tipo' => 'Formulario Tributario',
                'url' => $perfilInversor->url_formulario_tributario,
                'estado' => 'Completado'
            ];
        }

        if ($perfilInversor->url_contrato_inversion_marco) {
            $documents[] = [
                'tipo' => 'Contrato de Inversión',
                'url' => $perfilInversor->url_contrato_inversion_marco,
                'estado' => 'Completado'
            ];
        }

        if ($perfilInversor->url_perfil_riesgo) {
            $documents[] = [
                'tipo' => 'Perfil de Riesgo',
                'url' => $perfilInversor->url_perfil_riesgo,
                'estado' => 'Completado'
            ];
        }

        if ($perfilInversor->url_verificacion_direccion) {
            $documents[] = [
                'tipo' => 'Verificación de Dirección',
                'url' => $perfilInversor->url_verificacion_direccion,
                'estado' => 'Completado'
            ];
        }

        return $documents;
    }

    /**
     * Get worker documents
     */
    private function getTrabajadorDocuments($perfilTrabajador)
    {
        $documents = [];

        if ($perfilTrabajador->url_id_oficial) {
            $documents[] = [
                'tipo' => 'ID Oficial',
                'url' => $perfilTrabajador->url_id_oficial,
                'estado' => 'Completado'
            ];
        }

        if ($perfilTrabajador->url_certificados_capacitacion) {
            $documents[] = [
                'tipo' => 'Certificados de Capacitación',
                'url' => $perfilTrabajador->url_certificados_capacitacion,
                'estado' => 'Completado'
            ];
        }

        if ($perfilTrabajador->url_curriculum) {
            $documents[] = [
                'tipo' => 'Currículum',
                'url' => $perfilTrabajador->url_curriculum,
                'estado' => 'Completado'
            ];
        }

        if ($perfilTrabajador->url_foto_carnet) {
            $documents[] = [
                'tipo' => 'Foto Carnet',
                'url' => $perfilTrabajador->url_foto_carnet,
                'estado' => 'Completado'
            ];
        }

        return $documents;
    }

    /**
     * Get constructor documents
     */
    private function getConstructorDocuments($perfilConstructor)
    {
        $documents = [];

        if ($perfilConstructor->url_certificado_registro_empresa) {
            $documents[] = [
                'tipo' => 'Certificado de Registro de Empresa',
                'url' => $perfilConstructor->url_certificado_registro_empresa,
                'estado' => 'Completado'
            ];
        }

        if ($perfilConstructor->url_licencia_contratista) {
            $documents[] = [
                'tipo' => 'Licencia de Contratista',
                'url' => $perfilConstructor->url_licencia_contratista,
                'estado' => 'Completado'
            ];
        }

        if ($perfilConstructor->url_seguro_responsabilidad) {
            $documents[] = [
                'tipo' => 'Seguro de Responsabilidad',
                'url' => $perfilConstructor->url_seguro_responsabilidad,
                'estado' => 'Completado'
            ];
        }

        if ($perfilConstructor->url_seguro_compensacion) {
            $documents[] = [
                'tipo' => 'Seguro de Compensación',
                'url' => $perfilConstructor->url_seguro_compensacion,
                'estado' => 'Completado'
            ];
        }

        if ($perfilConstructor->url_portafolio_proyectos) {
            $documents[] = [
                'tipo' => 'Portafolio de Proyectos',
                'url' => $perfilConstructor->url_portafolio_proyectos,
                'estado' => 'Completado'
            ];
        }

        return $documents;
    }

    /**
     * Update investor document
     */
    private function updateInversorDocument($perfilInversor, $tipoDocumento, $filePath)
    {
        $updateData = [];

        switch ($tipoDocumento) {
            case 'id_oficial':
                $updateData['url_id_oficial'] = $filePath;
                break;
            case 'prueba_fondos':
                $updateData['url_prueba_fondos'] = $filePath;
                break;
            case 'formulario_tributario':
                $updateData['url_formulario_tributario'] = $filePath;
                break;
            case 'contrato_inversion':
                $updateData['url_contrato_inversion_marco'] = $filePath;
                break;
            case 'perfil_riesgo':
                $updateData['url_perfil_riesgo'] = $filePath;
                break;
            case 'verificacion_direccion':
                $updateData['url_verificacion_direccion'] = $filePath;
                break;
        }

        if (!empty($updateData)) {
            $perfilInversor->update($updateData);
        }
    }

    /**
     * Update worker document
     */
    private function updateTrabajadorDocument($perfilTrabajador, $tipoDocumento, $filePath)
    {
        $updateData = [];

        switch ($tipoDocumento) {
            case 'id_oficial':
                $updateData['url_id_oficial'] = $filePath;
                break;
            case 'certificados':
                $updateData['url_certificados_capacitacion'] = $filePath;
                break;
            case 'curriculum':
                $updateData['url_curriculum'] = $filePath;
                break;
            case 'foto_carnet':
                $updateData['url_foto_carnet'] = $filePath;
                break;
        }

        if (!empty($updateData)) {
            $perfilTrabajador->update($updateData);
        }
    }

    /**
     * Update constructor document
     */
    private function updateConstructorDocument($perfilConstructor, $tipoDocumento, $filePath)
    {
        $updateData = [];

        switch ($tipoDocumento) {
            case 'certificado_registro':
                $updateData['url_certificado_registro_empresa'] = $filePath;
                break;
            case 'licencia_contratista':
                $updateData['url_licencia_contratista'] = $filePath;
                break;
            case 'seguro_responsabilidad':
                $updateData['url_seguro_responsabilidad'] = $filePath;
                break;
            case 'seguro_compensacion':
                $updateData['url_seguro_compensacion'] = $filePath;
                break;
            case 'portafolio':
                $updateData['url_portafolio_proyectos'] = $filePath;
                break;
        }

        if (!empty($updateData)) {
            $perfilConstructor->update($updateData);
        }
    }
}
