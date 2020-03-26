<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\Rate;
use App\Form\PeriodType;
use App\Form\RateSearchType;
use App\Repository\RateRepository;
use App\Services\CurrencyRate\RateImport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * Главная страничка
     * Ничего не делает
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    /**
     * Просмотр курсов в виде таблицы
     *
     * @Route("/table", name="table")
     * @param Request $request
     * @param RateRepository $rateRepository
     * @return Response
     */
    public function table(Request $request, RateRepository $rateRepository)
    {
        $form = $this->createForm(RateSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $currentPage = $request->get('page', 1);
            $sort = $request->get('sort', 'date');
            $dir = $request->get('dir', 'asc');


            $queryResult = $rateRepository->findByDateAndCurrency($formData['currency'],
                $formData['from'],
                $formData['to'],
                RateRepository::PER_PAGE_100,
                $currentPage,
                $sort,
                $dir);

            return $this->render('index/table.html.twig', [
                'form' => $form->createView(),
                'paginator' => $queryResult['paginator'],
                'totalItems' => $queryResult['totalItems'],
                'pagesCount' => $queryResult['pagesCount'],
                'currentPage' => $currentPage,
            ]);
        }

        return $this->render('index/table.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Просмотр курса валюты на графике
     *
     * @Route("/chart", name="chart")
     * @param Request $request
     * @param RateRepository $rateRepository
     * @return Response
     */
    public function chart(Request $request, RateRepository $rateRepository) {
        $form = $this->createForm(RateSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $queryResult = $rateRepository->findByDateAndCurrency($formData['currency'],
                $formData['from'],
                $formData['to'],
                RateRepository::PER_PAGE_ALL);

            $labels = $rates = [];
            /** @var Rate $rate */
            foreach ($queryResult['paginator'] as $rate) {
                $labels[] = $rate->getDate()->format('d.m.Y');
                $rates[] = $rate->getRate();
            }

            return $this->render('index/chart.html.twig', [
                'form' => $form->createView(),
                'currency' => $formData['currency'],
                'labels' => $labels,
                'rates' => $rates,
            ]);
        }

        return $this->render('index/chart.html.twig', [
            'form' => $form->createView(),
            'currency' => null,
            'labels' => null,
            'rates' => null,
        ]);
    }

    /**
     * Импортирование данных из внешнего источника
     *
     * @Route("/import", name="import")
     * @param Request $request
     * @param RateImport $rateImport
     * @return Response
     * @throws \Exception
     */
    public function import(Request $request, RateImport $rateImport) {
        $form = $this->createForm(PeriodType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $errors = [];
            $result = [];
            try {
                $result = $rateImport->import($formData['from'], $formData['to']);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }

            return $this->render('index/import.html.twig', [
                'form' => $form->createView(),
                'result' => $result,
                'errors' => $errors
            ]);
        }

        return $this->render('index/import.html.twig', [
            'form' => $form->createView(),
            'result' => []
        ]);
    }



    /**
     * @Route("/download", name="download")
     * @param Request $request
     * @param RateRepository $rateRepository
     * @return Response
     */
    public function download(Request $request, RateRepository $rateRepository) {
        $form = $this->createForm(RateSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $queryResult = $rateRepository->findByDateAndCurrency($formData['currency'],
                $formData['from'],
                $formData['to'],
                RateRepository::PER_PAGE_ALL);

            $rates = [];
            /** @var Rate $rate */
            foreach ($queryResult['paginator'] as $rate) {
                $rates[] = [
                    'currency' => $rate->getCurrency()->getName(),
                    'date' => $rate->getDate()->format('d.m.Y'),
                    'rate' => $rate->getRate(),
                ];
            }

            $filename = "rates.json";
            $response = new Response(json_encode($rates));
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );

            $response->headers->set('Content-Type', 'application/json; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;
        }

        return $this->render('index/chart.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
