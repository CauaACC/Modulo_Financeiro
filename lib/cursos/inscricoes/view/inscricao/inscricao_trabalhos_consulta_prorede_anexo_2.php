<!-- path: /mnt/data/pontuacao_pes1_anuente_pdf.html -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Anexo II - Profissional Especializado de Suporte 1 — Pontuação de Currículo</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    :root { --w: 1100px; --pad: 10px; --radius: 10px; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, Helvetica, sans-serif; background: #f7f7f7; color: #222; }
    .wrap { max-width: var(--w); margin: 24px auto; background: #fff; padding: 18px; border-radius: var(--radius); box-shadow: 0 2px 10px rgba(0,0,0,.06); }
    h1,h2 { margin: 0 0 8px; }
    h1 { font-size: 18px; }
    h2 { font-size: 16px; margin-top: 18px; }
    .actions { display:flex; gap:8px; flex-wrap: wrap; margin: 8px 0 16px; }
    button { padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; cursor: pointer; background: #fafafa; }
    button:hover { background: #f0f0f0; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #ddd; padding: var(--pad); vertical-align: top; }
    th { background: #fafafa; text-align: center; }
    td:nth-child(1) { width: 56%; }
    td:nth-child(2), td:nth-child(3) { width: 10%; text-align:center; }
    td:nth-child(4) { width: 14%; text-align:center; }
    td:nth-child(5) { width: 10%; text-align:center; }
    input[type="number"] { width: 80px; padding: 6px; }
    input[readonly] { background: #fcfcfc; border: none; width: 90px; font-weight: 600; }
    .muted { color: #666; font-size: 12px; }
    .cap-hit { background: #fff6e5; }
    tfoot td { font-weight: 700; }
    .note { font-size: 12px; color: #444; }
    @media print {
      body { background: #fff; }
      .wrap { box-shadow: none; }
      .actions { display:none; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>ANEXO II — Profissional Especializado de Suporte 1 — PONTUAÇÃO DE CURRÍCULO</h1>
    <a href='https://hutec.org.br/wp-content/uploads/2025/09/2.-ANEXO-2-Formulario-curriculo-PES1-1.pdf' target='_blank'>Abrir PDF do Anexo II</a>
    <!-- <p class="muted">Baseado no PDF “2.-ANEXO-2-Formulario-curriculo-PES1-1.pdf”.</p> --> -->

    <div class="actions">
      <button id="btnClear" type="button">Limpar</button>
      <!-- <button id="btnPrint" type="button">Imprimir</button> -->
    </div>

    <table id="tbl">
      <thead>
        <tr>
          <th>Itens</th>
          <th>Pontos<br/>por item</th>
          <th>Máx.<br/>pontos</th>
          <th>Quantidade<br/><span class="muted">sugerida</span></th>
          <th>Pontos<br/>calculados</th>
        </tr>
      </thead>
      <tbody>

        <!-- 1. FORMAÇÃO -->
        <tr><td colspan="5"><strong>1. FORMAÇÃO</strong></td></tr>

        <!-- 1.1 -->
        <tr data-id="1_1" data-per="10" data-max="10" data-qtycap="1">
          <td>1.1 Ensino Médio com magistério</td>
          <td>10,0</td><td>10</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.2 -->
        <tr data-id="1_2" data-per="5" data-max="5" data-qtycap="1">
          <td>1.2 Curso técnico de enfermagem</td>
          <td>5,0</td><td>5</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.3 -->
        <tr data-id="1_3" data-per="5" data-max="5" data-qtycap="1">
          <td>1.3 Cursando graduação (Pedagogia, Ed. Física, Enferm., Fisio., Fono., Nutri., Psic., TO)</td>
          <td>5,0</td><td>5</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.4.1 -->
        <tr data-id="1_4_1" data-per="15" data-max="15" data-qtycap="1" data-mutual="grupo_1_4">
          <td>1.4.1 Graduação completa (mesmas áreas do item 1.3)</td>
          <td>15,0</td><td>15</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.4.2 -->
        <tr data-id="1_4_2" data-per="15" data-max="15" data-qtycap="1" data-mutual="grupo_1_4">
          <td>1.4.2 Graduação completa com licenciatura <span class="muted">(Não pontuar junto com 1.4.1)</span></td>
          <td>15,0</td><td>15</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.5 -->
        <tr data-id="1_5" data-per="2" data-max="6" data-qtycap="3">
          <td>1.5 Curso complementar em AC/educação infantil/especial — até 20h</td>
          <td>2,0</td><td>6</td>
          <td><input type="number" min="0" max="3" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.6 -->
        <tr data-id="1_6" data-per="4" data-max="12" data-qtycap="3">
          <td>1.6 Curso complementar em AC/educação infantil/especial — &gt; 20h</td>
          <td>4,0</td><td>12</td>
          <td><input type="number" min="0" max="3" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.7.1 -->
        <tr data-id="1_7_1" data-per="6" data-max="6" data-qtycap="1">
          <td>1.7.1 Cursando Especialização/Pós lato sensu (AC/educação infantil/especial)</td>
          <td>6,0</td><td>6</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.7.2 -->
        <tr data-id="1_7_2" data-per="10" data-max="50" data-qtycap="5">
          <td>1.7.2 Residência/Especialização/Pós lato sensu concluída (AC/educação infantil/especial)</td>
          <td>10,0</td><td>50</td>
          <td><input type="number" min="0" max="5" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.8.1 -->
        <tr data-id="1_8_1" data-per="15" data-max="15" data-qtycap="1">
          <td>1.8.1 Cursando Mestrado (Psic., Educação, Desenvolvimento, AC)</td>
          <td>15,0</td><td>15</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.8.2 -->
        <tr data-id="1_8_2" data-per="20" data-max="20" data-qtycap="1">
          <td>1.8.2 Mestrado/Dissertação nas áreas citadas</td>
          <td>20,0</td><td>20</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.9.1 -->
        <tr data-id="1_9_1" data-per="25" data-max="25" data-qtycap="1">
          <td>1.9.1 Cursando Doutorado (Psic., Educação, Desenvolvimento, AC)</td>
          <td>25,0</td><td>25</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 1.9.2 -->
        <tr data-id="1_9_2" data-per="30" data-max="30" data-qtycap="1">
          <td>1.9.2 Doutorado/Tese nas áreas citadas</td>
          <td>30,0</td><td>30</td>
          <td><input type="number" min="0" max="1" step="1" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 2. FORMAÇÃO ACADÊMICA -->
        <tr><td colspan="5"><strong>2. FORMAÇÃO ACADÊMICA</strong></td></tr>

        <!-- 2.1 -->
        <tr data-id="2_1" data-per="0.5" data-max="30">
          <td>2.1 Projetos de Ensino/Pesquisa/Extensão (IES) nas áreas de AC/educação infantil/especial</td>
          <td>0,5/mês</td><td>30</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 2.2 -->
        <tr data-id="2_2" data-per="0.1" data-max="15">
          <td>2.2 Grupos de estudos nas áreas de AC/educação infantil/especial</td>
          <td>0,1/hora</td><td>15</td>
          <td><input type="number" min="0" step="1" placeholder="horas" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 3. EXPERIÊNCIA PROFISSIONAL -->
        <tr><td colspan="5"><strong>3. EXPERIÊNCIA PROFISSIONAL</strong></td></tr>

        <!-- 3.1 -->
        <tr data-id="3_1" data-per="0.5" data-max="30">
          <td>3.1 Exercício profissional no Ensino Fundamental/Médio (não docente) no campo da Psicologia escolar</td>
          <td>0,5/mês</td><td>30</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 3.2 -->
        <tr data-id="3_2" data-per="0.8" data-max="48">
          <td>3.2 Exercício profissional em educação especial (não docente) no campo da Psicologia escolar</td>
          <td>0,8/mês</td><td>48</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

        <!-- 3.3 (PDF repete 3.2, aqui normalizamos como 3.3) -->
        <tr data-id="3_3" data-per="1" data-max="60">
          <td>3.3 Atendimento/atuação como AT na clínica com população infantil</td>
          <td>1,0/mês</td><td>60</td>
          <td><input type="number" min="0" step="1" placeholder="meses" /></td>
          <td><input type="text" readonly /></td>
        </tr>

      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="text-align:right;">TOTAL CALCULADO</td>
          <td><input id="total" type="text" readonly /></td>
        </tr>
      </tfoot>
    </table>

    <p class="note">
      Regras: aplica-se teto de pontos por linha (“Máx. pontos”). Itens 1.4.1 e 1.4.2 são mutuamente excludentes.
    </p>
  </div>

  <script>
    (function () {
      const tbl = document.getElementById('tbl');
      const totalEl = document.getElementById('total');
      const LS_KEY = 'pes1_pontuacao_v1';

      function parse(n) { return isNaN(n) ? 0 : n; }

      function calcRow(qty, per, max) {
        const pts = qty * per;
        return Math.min(pts, max);
      }

      function updateMutualExclusion() {
        const groups = {};
        tbl.querySelectorAll('tbody tr[data-mutual]').forEach(tr => {
          const g = tr.dataset.mutual;
          if (!groups[g]) groups[g] = [];
          groups[g].push(tr);
        });
        Object.values(groups).forEach(rows => {
          const someActive = rows.some(tr => parseFloat(tr.querySelector('input[type="number"]').value || '0') > 0);
          rows.forEach(tr => {
            const inp = tr.querySelector('input[type="number"]');
            const othersActive = rows.some(other => other !== tr && parseFloat(other.querySelector('input[type="number"]').value || '0') > 0);
            inp.disabled = othersActive && parseFloat(inp.value || '0') === 0;
            tr.classList.toggle('cap-hit', othersActive && parseFloat(inp.value || '0') === 0);
          });
        });
      }

      function applyRow(tr) {
        const per = parseFloat(tr.dataset.per);
        const max = parseFloat(tr.dataset.max);
        const qtycap = tr.dataset.qtycap ? parseFloat(tr.dataset.qtycap) : null;
        const qtyInput = tr.querySelector('input[type="number"]');
        const out = tr.querySelector('input[readonly]');

        let qty = parseFloat(qtyInput.value || '0');
        if (qtycap !== null && qty > qtycap) qty = qtycap;
        if (qty < 0) qty = 0;

        const pts = calcRow(qty, per, max);
        out.value = pts.toFixed(2).replace('.', ',');
        tr.classList.toggle('cap-hit', (qty * per) > max || (qtycap !== null && qtyInput.value > qtycap));
      }

      function recalcAll() {
        let total = 0;
        tbl.querySelectorAll('tbody tr[data-id]').forEach(tr => {
          applyRow(tr);
          const val = tr.querySelector('input[readonly]').value.replace('.', '').replace(',', '.');
          total += parseFloat(val || '0');
        });
        totalEl.value = total.toFixed(2).replace('.', ',');
        updateMutualExclusion();
        saveLS();
      }

      function saveLS() {
        const data = {};
        tbl.querySelectorAll('tbody tr[data-id]').forEach(tr => {
          const id = tr.dataset.id;
          const qty = tr.querySelector('input[type="number"]').value || '';
          data[id] = qty;
        });
        localStorage.setItem(LS_KEY, JSON.stringify(data));
      }

      function restoreLS() {
        try {
          const raw = localStorage.getItem(LS_KEY);
          if (!raw) return;
          const data = JSON.parse(raw);
          tbl.querySelectorAll('tbody tr[data-id]').forEach(tr => {
            const id = tr.dataset.id;
            const qty = data[id];
            if (qty !== undefined) tr.querySelector('input[type="number"]').value = qty;
          });
        } catch (_) {}
      }

      function clearAll() {
        tbl.querySelectorAll('tbody tr[data-id] input[type="number"]').forEach(inp => { inp.value = ''; });
        localStorage.removeItem(LS_KEY);
        recalcAll();
      }

      // Events
      tbl.addEventListener('input', (e) => {
        if (e.target.matches('input[type="number"]')) recalcAll();
      });

      document.getElementById('btnClear').addEventListener('click', clearAll);
      document.getElementById('btnPrint').addEventListener('click', () => window.print());

      // Init
      restoreLS();
      recalcAll();
    })();
  </script>
</body>
</html>