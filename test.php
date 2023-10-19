// ...
use App\Entity\Gestionnaire;
use App\Entity\Employeur;
use App\Entity\Formateur;
use App\Entity\Stagiaire;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        // ...

        if ($form->isSubmitted() && $form->isValid()) {
            // ...

            $role = $form->get('role')->getData();

            switch ($role) {
                case 'ROLE_GESTIONNAIRE':
                    $user = new Gestionnaire();
                    break;
                case 'ROLE_EMPLOYEUR':
                    $user = new Employeur();
                    break;
                case 'ROLE_FORMATEUR':
                    $user = new Formateur();
                    break;
                case 'ROLE_STAGIAIRE':
                    $user = new Stagiaire();
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid role.');
            }

            // Set the common user properties
            $user->setEmail($form->get('email')->getData());
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            // Handle additional properties specific to each profile

            if ($user instanceof Gestionnaire) {
                // Handle Gestionnaire properties
            } elseif ($user instanceof Employeur) {
                // Handle Employeur properties
            } elseif ($user instanceof Formateur) {
                // Handle Formateur properties
            } elseif ($user instanceof Stagiaire) {
                // Handle Stagiaire properties
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // ...
        }

        // ...
    }
}
