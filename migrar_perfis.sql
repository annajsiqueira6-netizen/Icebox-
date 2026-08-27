-- ============================================================
-- MIGRAÇÃO: expande tipo_usuario de 2 para 3 perfis
-- admin/comum  ->  administrador / comissao / jogador
-- Rode isso UMA VEZ no seu banco atual (aba SQL do phpMyAdmin,
-- com o banco moneyball_valorant já selecionado)
-- ============================================================

-- 1) Amplia o ENUM temporariamente pra caber os valores antigos e novos
ALTER TABLE usuarios
  MODIFY tipo_usuario ENUM('admin','comum','administrador','comissao','jogador')
  NOT NULL DEFAULT 'jogador';

-- 2) Converte os dados existentes para os novos valores
UPDATE usuarios SET tipo_usuario = 'administrador' WHERE tipo_usuario = 'admin';
UPDATE usuarios SET tipo_usuario = 'jogador'        WHERE tipo_usuario = 'comum';

-- 3) Restringe o ENUM só aos 3 novos valores
ALTER TABLE usuarios
  MODIFY tipo_usuario ENUM('administrador','comissao','jogador')
  NOT NULL DEFAULT 'jogador';
