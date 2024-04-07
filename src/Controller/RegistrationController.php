<?php

namespace App\Controller;

use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Infrastructural\KnpUOAuth\Service\SocialLoginService;
use App\Repository\UserRepository;
use App\Security\Authenticator;
use App\Security\EmailVerifier;
use App\Service\IpService;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly SettingService $settingervice,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserAuthenticatorInterface $userAuthenticator,
        private readonly Authenticator $authenticator,
        private readonly SocialLoginService $socialLoginService,
        private readonly IpService $ipService,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route('/signup', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            $this->addFlash('danger', $this->translator->trans('Already logged in'));

            return $this->redirectToRoute('home');
        }

        $appErrors = [];

        $user = new User();
        $registrationForm = $this->createForm(RegistrationFormType::class, $user);

        if (0 == $this->settingervice->getSettings('google_recaptcha_enabled')) {
            $registrationForm->remove('recaptcha');
        }

        $registrationForm->setData($user);
        $registrationForm->remove('restaurant');

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            // encode the plain password
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $registrationForm->get('plainPassword')->getData()
                )
            );

            $user->addRole(HasRoles::CREATOR);
            $user->setLastLoginIp($request->getClientIp());

            $this->em->persist($user);
            $this->em->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address(
                        $this->getParameter('website_no_reply_email'),
                        $this->getParameter('website_name'),
                    ))
                    ->to($user->getEmail())
                    ->subject($this->translator->trans('Please Confirm your Email'))
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->userAuthenticator->authenticateUser(
                $user,
                $this->authenticator,
                $request
            );

        // return $this->redirectToRoute('verify');
        } elseif ($registrationForm->isSubmitted()) {
            /** @var FormError $error */
            foreach ($registrationForm->getErrors() as $error) {
                if (null === $error->getCause()) {
                    $appErrors[] = $error;
                }
            }
        }

        return $this->render('registration/register.html.twig', [
            'errors' => $appErrors,
            'user' => $user,
            'registrationForm' => $registrationForm,
        ]);
    }

    #[Route('/signup-restaurant', name: 'register_restaurant', methods: ['GET', 'POST'])]
    public function registerRestaurant(Request $request): Response
    {
        if ($this->getUser()) {
            $this->addFlash('danger', $this->translator->trans('Already logged in'));

            return $this->redirectToRoute('home');
        }

        $appErrors = [];

        $user = new User();
        $registrationForm = $this->createForm(RegistrationFormType::class, $user);

        if (0 == $this->settingervice->getSettings('google_recaptcha_enabled')) {
            $registrationForm->remove('recaptcha');
        }

        $registrationForm->setData($user);
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            // encode the plain password
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $registrationForm->get('plainPassword')->getData()
                )
            );

            $user->addRole(HasRoles::RESTAURANT);
            $user->getRestaurant()->getUser($user);
            $user->setLastLoginIp($request->getClientIp());

            $this->em->persist($user);
            $this->em->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address(
                        $this->getParameter('website_no_reply_email'),
                        $this->getParameter('website_name'),
                    ))
                    ->to($user->getEmail())
                    ->subject($this->translator->trans('Please Confirm your Email'))
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $this->userAuthenticator->authenticateUser(
                $user,
                $this->authenticator,
                $request
            );

        // return $this->redirectToRoute('verify');
        } elseif ($registrationForm->isSubmitted()) {
            /** @var FormError $error */
            foreach ($registrationForm->getErrors() as $error) {
                if (null === $error->getCause()) {
                    $appErrors[] = $error;
                }
            }
        }

        return $this->render('registration/register-restaurant.html.twig', [
            'errors' => $appErrors,
            'user' => $user,
            'registrationForm' => $registrationForm,
        ]);
    }

    #[Route('/verify/email', name: 'verify_email', methods: ['GET', 'POST'])]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /*
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }
        */

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('primary', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('dashboard_main');
        }

        $this->addFlash('success', $this->translator->trans('Your email address has been verified.'));

        return $this->redirectToRoute('dashboard_main');
    }

    #[Route('/verify', name: 'verify', methods: ['GET'])]
    public function verify(): Response
    {
        return $this->render('registration/verify.html.twig');
    }
}
