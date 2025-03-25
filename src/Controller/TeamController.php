<?php

namespace App\Controller;

use Pimcore\Model\DataObject\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    #[Route('/teams', name: 'team_list')]
    public function list(): Response
    {
        $teams = new Team\Listing();
        $teams->setOrderKey('teamName');
        $teams->setOrder('asc');

        return $this->render('teams.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/teams/{id}', name: 'team_detail')]
    public function detail(int $id): Response
    {
        $team = Team::getById($id);

        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }

        return $this->render('team_detail.html.twig', [
            'team' => $team
        ]);
    }
}
