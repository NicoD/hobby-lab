import { useState, useEffect } from 'react';

const LS_KEY = 'form_foo_bar';

function validate(fields) {
  const errors = {};

  if (!/^-?\d+$/.test(fields.foo.trim())) {
    errors.foo = 'Foo doit être un entier (ex: 42, -3)';
  }

  if (fields.bar.length >= 5) {
    errors.bar = `Bar doit faire moins de 5 caractères (actuellement ${fields.bar.length})`;
  }

  return errors;
}

export default function FooBarForm() {
  const [fields, setFields] = useState({ foo: '', bar: '' });
  const [errors, setErrors] = useState({});
  const [apiError, setApiError] = useState(null);
  const [success, setSuccess] = useState(false);
  const [loading, setLoading] = useState(false);

  // Préremplissage depuis le localStorage
  useEffect(() => {
    try {
      const saved = localStorage.getItem(LS_KEY);
      if (saved) {
        setFields(JSON.parse(saved));
      }
    } catch (e) {
      console.warn('localStorage read error', e);
    }
  }, []);

  function handleChange(e) {
    const { name, value } = e.target;

    setFields(prev => ({ ...prev, [name]: value }));

    // Efface l'erreur du champ dès que l'utilisateur corrige
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: undefined }));
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setApiError(null);
    setSuccess(false);

    const validationErrors = validate(fields);

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    // Persiste les valeurs valides
    try {
      localStorage.setItem(LS_KEY, JSON.stringify(fields));
    } catch (e) {
      console.warn('localStorage write error', e);
    }

    setLoading(true);

    try {
      const response = await fetch('https://example.com', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          foo: parseInt(fields.foo, 10),
          bar: fields.bar,
        }),
      });

      if (!response.ok) {
        throw new Error(`Erreur serveur : ${response.status}`);
      }

      setSuccess(true);
    } catch (err) {
      setApiError(err.message || 'Une erreur est survenue.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate>

      {apiError && (
        <div role="alert" style={{ color: 'red', marginBottom: 16 }}>
          {apiError}
        </div>
      )}

      {success && (
        <div role="status" style={{ color: 'green', marginBottom: 16 }}>
          Ressource créée avec succès.
        </div>
      )}

      <div style={{ marginBottom: 16 }}>
        <label htmlFor="foo">Foo (entier)</label>
        <input
          id="foo"
          name="foo"
          type="text"
          value={fields.foo}
          onChange={handleChange}
          aria-invalid={!!errors.foo}
          aria-describedby={errors.foo ? 'foo-error' : undefined}
        />
        {errors.foo && (
          <span id="foo-error" role="alert" style={{ color: 'red', fontSize: 12 }}>
            {errors.foo}
          </span>
        )}
      </div>

      <div style={{ marginBottom: 16 }}>
        <label htmlFor="bar">Bar (max 4 caractères)</label>
        <input
          id="bar"
          name="bar"
          type="text"
          value={fields.bar}
          onChange={handleChange}
          aria-invalid={!!errors.bar}
          aria-describedby={errors.bar ? 'bar-error' : undefined}
        />
        {errors.bar && (
          <span id="bar-error" role="alert" style={{ color: 'red', fontSize: 12 }}>
            {errors.bar}
          </span>
        )}
      </div>

      <button type="submit" disabled={loading}>
        {loading ? 'Envoi en cours…' : 'Enregistrer'}
      </button>

    </form>
  );
}