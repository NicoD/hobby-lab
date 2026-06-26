import { useRouteError, isRouteErrorResponse, Link } from 'react-router-dom';

export default function RouterError() {
  const error = useRouteError();

  const is404 = isRouteErrorResponse(error) && error.status === 404;

  return (
    <div className="flex flex-col items-center justify-center h-full py-24 gap-4">
      <h1 className="text-3xl font-semibold text-gray-800">
        {is404 ? 'Page introuvable' : 'Une erreur est survenue'}
      </h1>
      <Link to="/" className="text-indigo-600 hover:underline">
        Retour à l'accueil
      </Link>
    </div>
  );
}
