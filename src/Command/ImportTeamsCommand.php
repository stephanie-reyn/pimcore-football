<?php

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pimcore\Model\DataObject\Player;
use Pimcore\Model\DataObject\Team;
use Pimcore\Model\DataObject\Data\GeoCoordinates;
use Pimcore\Model\DataObject;

class ImportTeamsCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('app:import-teams')
            ->setDescription('Imports teams and players from CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csvFile = PIMCORE_PROJECT_ROOT . '/var/import/teams.csv';

        if (!file_exists($csvFile)) {
            $output->writeln('<error>CSV file not found at var/import/teams.csv</error>');
            return self::FAILURE;
        }

        $rows = array_map('str_getcsv', file($csvFile));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (count($row) !== count($header)) {
                $output->writeln('<comment>Skipping malformed row: ' . implode(',', $row) . '</comment>');
                continue;
            }

            $data = array_combine($header, $row);
            $teamKey = strtolower(str_replace(' ', '-', $data['team_name']));

            // Check if team already exists
            $team = DataObject::getByPath('/Teams/' . $teamKey);
            if (!$team) {
                $team = new Team();
                $team->setKey($teamKey);

                // Ensure /Teams folder exists
                $teamsFolder = DataObject::getByPath('/Teams');
                if (!$teamsFolder) {
                    $teamsFolder = new DataObject\Folder();
                    $teamsFolder->setKey('Teams');
                    $teamsFolder->setParentId(1);
                    $teamsFolder->save();
                }

                $team->setParent($teamsFolder);
                $team->setTeamName($data['team_name']);
                $team->setCouch($data['coach']);
                $team->setCity($data['city']);

                $location = new GeoCoordinates();
                $location->setLatitude((float) $data['latitude']);
                $location->setLongitude((float) $data['longitude']);
                $team->setLocation($location);

                $team->setFoundedIn((int) $data['founded']);
                $team->setPublished(true);
                $team->save();

                $output->writeln("<info>✅ Created team: {$data['team_name']}</info>");
            } else {
                $output->writeln("<comment>⚠️ Skipping existing team: {$data['team_name']}</comment>");
            }

            // Create Player
            $playerKey = strtolower(str_replace(' ', '-', $data['player_name']));
            $player = new Player();
            $player->setKey($playerKey);

            // Ensure /Players folder exists
            $playersFolder = DataObject::getByPath('/Players');
            if (!$playersFolder) {
                $playersFolder = new DataObject\Folder();
                $playersFolder->setKey('Players');
                $playersFolder->setParentId(1);
                $playersFolder->save();
            }

            $player->setParent($playersFolder);
            $player->setName($data['player_name']);
            $player->setNumber((int) $data['player_number']);
            $player->setAge((int) $data['player_age']);
            $player->setPosition($data['player_position']);
            $player->setPublished(true);
            $player->save();

            // Link player to team
            $currentPlayers = $team->getPlayers() ?? [];
            $currentPlayers[] = $player;
            $team->setPlayers($currentPlayers);
            $team->save();

            $output->writeln("<info>→ Added player {$data['player_name']} to {$data['team_name']}</info>");
        }

        $output->writeln('<info>🏁 Import completed!</info>');
        return self::SUCCESS;
    }
}
