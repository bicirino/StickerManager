<?php
class Figurinha {
    public static function all(array $filters = []): array {
        $sql = "SELECT f.*, s.nome AS selecao_nome, s.sigla, s.confederacao,
                       p.nome AS posicao_nome, c.nome AS categoria_nome,
                       COALESCE(mc.quantidade,0) AS quantidade
                FROM figurinhas f
                JOIN categoria c ON c.id = f.categoria_id
                LEFT JOIN selecoes s ON s.id = f.selecao_id
                LEFT JOIN posicao p ON p.id = f.posicao_id
                LEFT JOIN minha_colecao mc ON mc.figurinha_id = f.id AND mc.usuario_id = :uid
                WHERE 1=1";
        $params = [':uid' => $filters['uid'] ?? 0];
        if (!empty($filters['selecao_id'])) { $sql .= " AND f.selecao_id = :sid"; $params[':sid'] = (int)$filters['selecao_id']; }
        if (!empty($filters['posicao_id'])) { $sql .= " AND f.posicao_id = :pid"; $params[':pid'] = (int)$filters['posicao_id']; }
        if (!empty($filters['categoria_id'])) { $sql .= " AND f.categoria_id = :cid"; $params[':cid'] = (int)$filters['categoria_id']; }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'obtidas')  $sql .= " AND COALESCE(mc.quantidade,0) > 0";
            if ($filters['status'] === 'faltantes')$sql .= " AND COALESCE(mc.quantidade,0) = 0";
            if ($filters['status'] === 'repetidas')$sql .= " AND COALESCE(mc.quantidade,0) > 1";
        }
        if (!empty($filters['busca'])) {
            $sql .= " AND (f.nome_jogador LIKE :q OR f.numero LIKE :q)";
            $params[':q'] = '%' . $filters['busca'] . '%';
        }
        $sql .= " ORDER BY c.id, s.nome, f.numero";
        $st = db()->prepare($sql); $st->execute($params);
        return $st->fetchAll();
    }
    public static function find(int $id): ?array {
        $st = db()->prepare("SELECT * FROM figurinhas WHERE id=?"); $st->execute([$id]);
        return $st->fetch() ?: null;
    }
    public static function create(array $d): int {
        $st = db()->prepare("INSERT INTO figurinhas (numero,nome_jogador,categoria_id,selecao_id,posicao_id,raridade) VALUES (?,?,?,?,?,?)");
        $st->execute([$d['numero'],$d['nome_jogador'],$d['categoria_id'],$d['selecao_id']?:null,$d['posicao_id']?:null,$d['raridade']]);
        return (int)db()->lastInsertId();
    }
    public static function update(int $id, array $d): void {
        $st = db()->prepare("UPDATE figurinhas SET numero=?,nome_jogador=?,categoria_id=?,selecao_id=?,posicao_id=?,raridade=? WHERE id=?");
        $st->execute([$d['numero'],$d['nome_jogador'],$d['categoria_id'],$d['selecao_id']?:null,$d['posicao_id']?:null,$d['raridade'],$id]);
    }
    public static function delete(int $id): void {
        $st = db()->prepare("DELETE FROM figurinhas WHERE id=?"); $st->execute([$id]);
    }
    public static function stats(int $uid): array {
        $total = (int)db()->query("SELECT COUNT(*) FROM figurinhas")->fetchColumn();
        $obtidas = (int)db()->query("SELECT COUNT(*) FROM minha_colecao WHERE usuario_id=$uid AND quantidade>0")->fetchColumn();
        $repetidas = (int)db()->query("SELECT COALESCE(SUM(quantidade-1),0) FROM minha_colecao WHERE usuario_id=$uid AND quantidade>1")->fetchColumn();
        return ['total'=>$total,'obtidas'=>$obtidas,'faltantes'=>$total-$obtidas,'repetidas'=>$repetidas];
    }
    public static function porSelecao(int $uid): array {
        $sql = "SELECT s.nome, s.sigla, COUNT(f.id) AS total,
                SUM(CASE WHEN COALESCE(mc.quantidade,0)>0 THEN 1 ELSE 0 END) AS obtidas
                FROM selecoes s
                LEFT JOIN figurinhas f ON f.selecao_id=s.id
                LEFT JOIN minha_colecao mc ON mc.figurinha_id=f.id AND mc.usuario_id=?
                GROUP BY s.id ORDER BY s.nome";
        $st = db()->prepare($sql); $st->execute([$uid]);
        return $st->fetchAll();
    }
}

class Colecao {
    public static function toggle(int $uid, int $fid): int {
        $st = db()->prepare("SELECT quantidade FROM minha_colecao WHERE usuario_id=? AND figurinha_id=?");
        $st->execute([$uid,$fid]); $q = $st->fetchColumn();
        if ($q === false) {
            $st = db()->prepare("INSERT INTO minha_colecao (usuario_id,figurinha_id,quantidade) VALUES (?,?,1)");
            $st->execute([$uid,$fid]); return 1;
        }
        $st = db()->prepare("UPDATE minha_colecao SET quantidade=quantidade+1 WHERE usuario_id=? AND figurinha_id=?");
        $st->execute([$uid,$fid]); return (int)$q + 1;
    }
    public static function ajustar(int $uid, int $fid, int $delta): int {
        $st = db()->prepare("SELECT quantidade FROM minha_colecao WHERE usuario_id=? AND figurinha_id=?");
        $st->execute([$uid,$fid]); $q = (int)($st->fetchColumn() ?: 0);
        $nova = max(0, $q + $delta);
        if ($nova === 0 && $q > 0) {
            $st = db()->prepare("DELETE FROM minha_colecao WHERE usuario_id=? AND figurinha_id=?"); $st->execute([$uid,$fid]);
        } elseif ($q === 0 && $nova > 0) {
            $st = db()->prepare("INSERT INTO minha_colecao (usuario_id,figurinha_id,quantidade) VALUES (?,?,?)"); $st->execute([$uid,$fid,$nova]);
        } else {
            $st = db()->prepare("UPDATE minha_colecao SET quantidade=? WHERE usuario_id=? AND figurinha_id=?"); $st->execute([$nova,$uid,$fid]);
        }
        return $nova;
    }

    public static function unselect(int $uid, int $fid): int {
        $st = db()->prepare("SELECT quantidade FROM minha_colecao WHERE usuario_id=? AND figurinha_id=?");
        $st->execute([$uid,$fid]); $q = (int)($st->fetchColumn() ?: 0);
        if ($q > 1) {
            $nova = $q - 1;
            $st = db()->prepare("UPDATE minha_colecao SET quantidade=? WHERE usuario_id=? AND figurinha_id=?");
            $st->execute([$nova,$uid,$fid]);
            return $nova;
        }
        if ($q === 1) {
            $st = db()->prepare("DELETE FROM minha_colecao WHERE usuario_id=? AND figurinha_id=?");
            $st->execute([$uid,$fid]);
        }
        return 0;
    }
}

class Lookup {
    public static function selecoes(): array { return db()->query("SELECT * FROM selecoes ORDER BY nome")->fetchAll(); }
    public static function posicoes(): array { return db()->query("SELECT * FROM posicao ORDER BY id")->fetchAll(); }
    public static function categorias(): array { return db()->query("SELECT * FROM categoria ORDER BY id")->fetchAll(); }
}
