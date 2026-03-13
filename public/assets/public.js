(() => {
  const getMeta = (name) => {
    const el = document.querySelector(`meta[name="${name}"]`);
    return el ? el.getAttribute('content') || '' : '';
  };

  const base = getMeta('app-base').replace(/\/$/, '');

  const buildUrl = (path) => {
    if (!path.startsWith('/')) return base ? `${base}/${path}` : `/${path}`;
    return base ? `${base}${path}` : path;
  };

  const initCpfValidation = () => {
    const cpfInput = document.querySelector('[data-cpf-input="1"]');
    if (!cpfInput) return;

    const cpfError = document.querySelector('[data-cpf-error="exists"]');
    const cpfInvalid = document.querySelector('[data-cpf-error="invalid"]');

    let checkTimeout = null;

    const isValidCPF = (cpf) => {
      const digits = String(cpf || '').replace(/\D/g, '');
      if (digits.length !== 11) return false;
      if (/^(\d)\1{10}$/.test(digits)) return false;

      let sum = 0;
      for (let i = 0, w = 10; i < 9; i += 1, w -= 1) {
        sum += parseInt(digits[i], 10) * w;
      }
      let rest = sum % 11;
      const d1 = rest < 2 ? 0 : 11 - rest;
      if (parseInt(digits[9], 10) !== d1) return false;

      sum = 0;
      for (let i = 0, w = 11; i < 10; i += 1, w -= 1) {
        sum += parseInt(digits[i], 10) * w;
      }
      rest = sum % 11;
      const d2 = rest < 2 ? 0 : 11 - rest;
      return parseInt(digits[10], 10) === d2;
    };

    const setHidden = (el, hidden) => {
      if (!el) return;
      if (hidden) {
        el.classList.add('hidden');
      } else {
        el.classList.remove('hidden');
      }
    };

    const checkCpfExists = async (cpfDigits) => {
      const res = await fetch(buildUrl('/api/check-cpf'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ cpf: cpfDigits }),
      });

      if (!res.ok) {
        throw new Error('invalid');
      }
      return res.json();
    };

    cpfInput.addEventListener('input', () => {
      let value = String(cpfInput.value || '').replace(/\D/g, '');
      if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      }
      cpfInput.value = value;

      if (checkTimeout) {
        window.clearTimeout(checkTimeout);
        checkTimeout = null;
      }

      const digits = value.replace(/\D/g, '');
      if (digits.length === 11) {
        if (!isValidCPF(digits)) {
          setHidden(cpfInvalid, false);
          setHidden(cpfError, true);
          cpfInput.setCustomValidity('CPF inválido');
          return;
        }

        setHidden(cpfInvalid, true);
        cpfInput.setCustomValidity('');

        checkTimeout = window.setTimeout(async () => {
          try {
            const data = await checkCpfExists(digits);
            if (data && data.exists) {
              setHidden(cpfError, false);
              cpfInput.setCustomValidity('CPF já cadastrado');
            } else {
              setHidden(cpfError, true);
              cpfInput.setCustomValidity('');
            }
          } catch {
            setHidden(cpfInvalid, false);
            setHidden(cpfError, true);
            cpfInput.setCustomValidity('CPF inválido');
          }
        }, 500);

        return;
      }

      setHidden(cpfInvalid, true);
      setHidden(cpfError, true);
      cpfInput.setCustomValidity('');
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    initCpfValidation();
  });
})();

