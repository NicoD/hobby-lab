import { ReactNode } from 'react';

type FormFieldProp = {
  label: string;
  error?: string | null;
  children: ReactNode;
};
export default function FormField({ label, error, children }: FormFieldProp) {
  return (
    <fieldset className="mb-4 min-w-0 border-0 p-0">
      {label && (
        <legend className="mb-1 text-xs font-medium uppercase tracking-wide text-gray-400">
          {label}
        </legend>
      )}
      {children}
      {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
    </fieldset>
  );
}
