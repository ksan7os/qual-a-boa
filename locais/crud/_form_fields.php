<?php
// parcial de campos do formulário (reuso em adicionar/editar)
$csrf = csrf_token();
?>
<input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
<input type="hidden" name="id_local" value="<?= isset($local['id_local']) ? (int)$local['id_local'] : '' ?>">

<div class="row">
  <input class="half" type="text" name="nome" placeholder="Nome" value="<?= htmlspecialchars($local['nome'] ?? '') ?>" required>

  <select name="tipo" class="half" required>
    <?php
      $tipos = tipos_locais();
      $sel = $local['tipo'] ?? '';
      foreach ($tipos as $t) {
        $s = $sel === $t ? 'selected' : '';
        echo "<option value=\"$t\" $s>$t</option>";
      }
    ?>
  </select>
</div>

<textarea class="full" name="descricao" placeholder="Sobre o local (descrição)"><?= htmlspecialchars($local['descricao'] ?? '') ?></textarea>

<input class="full" type="text" name="endereco" placeholder="Endereço completo" value="<?= htmlspecialchars($local['endereco'] ?? '') ?>" required>

<div class="row">
  <select name="faixa_preco" class="half" required>
    <?php
      $faixas = faixas_preco();
      $sel = $local['faixa_preco'] ?? '';
      foreach ($faixas as $f) {
        $s = $sel === $f ? 'selected' : '';
        echo "<option value=\"$f\" $s>$f</option>";
      }
    ?>
  </select>

  <input class="half" type="text" name="telefone" placeholder="Número (telefone)" value="<?= htmlspecialchars($local['telefone'] ?? '') ?>">
</div>

<div class="row">
  <input class="half" type="url" name="site" placeholder="Site (https://...)" value="<?= htmlspecialchars($local['site'] ?? '') ?>">
  <input class="half" type="email" name="email_contato" placeholder="E-mail" value="<?= htmlspecialchars($local['email_contato'] ?? '') ?>">
</div>

<input class="full" type="text" name="redes_sociais" placeholder="Redes sociais (links separados por vírgula)" value="<?= htmlspecialchars($local['redes_sociais'] ?? '') ?>">

<textarea class="full" name="horario_funcionamento" placeholder="Horário de funcionamento (ex.: Seg–Sex 12h–22h; Sáb–Dom 11h–23h)"><?= htmlspecialchars($local['horario_funcionamento'] ?? '') ?></textarea>

<textarea class="full" name="servicos" placeholder="Serviços / tags separados por vírgula"><?= htmlspecialchars($local['servicos'] ?? '') ?></textarea>

<?php if (!empty($local['imagem_capa'])): ?>
  <p>Imagem atual:</p>
  <img src="../../img/capa-locais/<?= htmlspecialchars($local['imagem_capa']) ?>" width="140" style="border-radius:8px;">
<?php endif; ?>
<input type="hidden" name="imagem_capa_atual" value="<?= htmlspecialchars($local['imagem_capa'] ?? '') ?>">
<input class="full" type="file" name="imagem_capa" accept="image/*">

<button class="btn btn-edit full" type="submit">Salvar</button>