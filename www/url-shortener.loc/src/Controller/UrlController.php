<?php

namespace App\Controller;

use App\Domain\UrlDomain;
use App\Entity\Url;
use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    const ERROR_NON_EXISTENT_HASH = "Non-existent hash.";

    private function getUrlDomain(): UrlDomain
    {
        $entityManager = $this->getDoctrine()->getManager();
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);

        return new UrlDomain($entityManager, $urlRepository);
    }

    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(Request $request): JsonResponse
    {
        $url = $request->get('url');

        $hash = $this->getUrlDomain()->encodeUrl($url);

        return $this->json([
            'hash' => $hash
        ]);
    }

    /**
     * @Route("/decode-url", name="decode_url")
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        $hash = $request->get('hash');

        $url = $this->getUrlDomain()->decodeUrl($hash);

        if (!$url)
            return $this->json([
                'error' => self::ERROR_NON_EXISTENT_HASH
            ]);

        return $this->json([
            'url' => $url
        ]);
    }

    /**
     * @Route("/redirect-by-hash", name="redirect_by_hash")
     * @return RedirectResponse | JsonResponse
     */
    public function redirectByHash(Request $request)
    {
        $hash = $request->get('hash');

        $url = $this->getUrlDomain()->decodeUrl($hash);

        if (!$url)
            return $this->json([
                'error' => self::ERROR_NON_EXISTENT_HASH
            ]);

        return $this->redirect($url);
    }
}
