<?php
namespace App\Models;

use System\Model\Model;

class UsuarioModulo extends Model
{
    protected $table = 'usuarios_modulos';
    protected $timestamps = true;

    public function __construct()
    {
    	parent::__construct();
    }

    public function usuariosModulosPorIdEmpresaEIdUsuario($idEmpresa, $idUsuario, $idPerfil)
    {
        # Busca o Modulo Empresas somente para perfis Super Admin
        $queryIdPerfil = "";
        if ($idPerfil != 1) {
            $queryIdPerfil = "AND modulos.id != 3";
        }

    	return $this->query("
    		SELECT modulos.id AS idModulo, usm.id AS idUsuarioModulo, 
    		usm.id_usuario AS idUsuario, usm.id_empresa AS idEmpresa, modulos.descricao AS nomeModulo,
            usm.consultar, usm.criar, usm.editar, usm.excluir, usm.created_at, usm.updated_at
            FROM usuarios_modulos AS usm
            INNER JOIN modulos ON usm.id_modulo = modulos.id
            WHERE usm.id_empresa = {$idEmpresa} AND usm.id_usuario = {$idUsuario} 
            {$queryIdPerfil}
    	");
    }

    public function usuariosModulosPorIdUsuarioEIdModulo($idUsuario, $idModulo)
    {
        return $this->queryGetOne("
            SELECT * FROM usuarios_modulos WHERE id_usuario = {$idUsuario}
            AND id_modulo = {$idModulo}
        ");
    }

    public function usuariosModulosPorIdUsuario($idUsuario)
    {
        $query = $this->query("
            SELECT modulos.id AS idModulo, modulos.descricao AS nomeModulo,
            usm.id AS idUsuarioModulo, usm.consultar, usm.criar, usm.editar, usm.excluir
            FROM usuarios_modulos AS usm INNER JOIN modulos ON usm.id_modulo = modulos.id 
            WHERE id_usuario = {$idUsuario}
        ");
        
        $dados = [];
        foreach ($query as $valor) {
            $dados[$valor->idModulo] = [$valor];
        }

        return $dados;
    }

    public function criarPermissoesAoCadstrarUsuario($modulos, $idUsuario, $idEmpresa, $perfilUsuario = false)
    {
        $permissoes = [];
        foreach ($modulos as $modulo) {
            $permissoes['id_usuario'] = $idUsuario;
            $permissoes['id_empresa'] = $idEmpresa;
            $permissoes['id_modulo'] = $modulo->id;
            $permissoes['consultar'] = true;
            $permissoes['criar'] = true;
            $permissoes['editar'] = true;
            $permissoes['excluir'] = true;

            $this->save($permissoes);
        }
    }

    public function salvarPermissoes($idUsuario, $idModulo, $tipoPermissao)
    {
        $dadosUsuarioModulo = $this->usuariosModulosPorIdUsuarioEIdModulo($idUsuario, $idModulo);

        if ($tipoPermissao == 'consultar') {
            if ($dadosUsuarioModulo->consultar) {
                $dados['consultar'] = false;
            } else {
                $dados['consultar'] = true;
            }
        }

        if ($tipoPermissao == 'criar') {
            if ($dadosUsuarioModulo->criar) {
                $dados['criar'] = false;
            } else {
                $dados['criar'] = true;
            }
        }

        if ($tipoPermissao == 'editar') {
            if ($dadosUsuarioModulo->editar) {
                $dados['editar'] = false;
            } else {
                $dados['editar'] = true;
            }
        }

        if ($tipoPermissao == 'excluir') {
            if ($dadosUsuarioModulo->excluir) {
                $dados['excluir'] = false;
            } else {
                $dados['excluir'] = true;
            }
        }

        if ($this->update($dados, $dadosUsuarioModulo->id)) {
            return true;
        }

        return false;
    }
}