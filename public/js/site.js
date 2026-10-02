/*
 * Comportamentos do site público — JS puro, sem build. A validação de verdade é do servidor
 * (StoreProposalRequest); aqui só há conveniências de interface.
 */
document.addEventListener('DOMContentLoaded', () => {
  // Telefone: (00) 0000-0000 ou (00) 00000-0000, conforme a quantidade de dígitos.
  document.querySelectorAll('[data-mask="phone"]').forEach((input) => {
    const format = () => {
      const digits = input.value.replace(/\D/g, '').slice(0, 11);
      const split = digits.length > 10 ? 7 : 6;
      let value = digits;
      if (digits.length > 2) value = `(${digits.slice(0, 2)}) ${digits.slice(2, split)}`;
      if (digits.length > split) value += `-${digits.slice(split)}`;
      input.value = value;
    };
    input.addEventListener('input', format);
    format();
  });

  // Evita envio duplo: desabilita o botão e mostra o texto de carregamento (antes "Enviando…").
  document.querySelectorAll('form[data-submit-once]').forEach((form) => {
    form.addEventListener('submit', () => {
      const button = form.querySelector('[type="submit"][data-loading-text]');
      if (!button) return;
      button.disabled = true;
      button.textContent = button.dataset.loadingText;
    });
  });
});
