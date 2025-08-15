<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Objects\SearchResult;
use NickMous\Binsta\Repositories\UserRepository;

class SearchController extends BaseController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Search for users based on a query.
     *
     * @param string $query The search query.
     * @return JsonResponse
     * @throws \JsonException
     */
    public function search(string $query): JsonResponse
    {
        $results = [];

        $usersFound = $this->userRepository->searchFor($query);

        foreach ($usersFound as $user) {
            $result = new SearchResult();
            $result->url = '/users/' . $user->username;
            $result->type = 'user';
            $result->title = $user->name;
            $result->subtext = $user->username;
            $results[] = $result;
        }

        return new JsonResponse([
            'results' => $results,
        ]);
    }
}
