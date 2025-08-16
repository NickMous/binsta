<?php

namespace NickMous\Binsta\Controllers;

use NickMous\Binsta\Entities\Post;
use NickMous\Binsta\Internals\BaseController;
use NickMous\Binsta\Internals\Response\JsonResponse;
use NickMous\Binsta\Repositories\PostRepository;
use NickMous\Binsta\Requests\Posts\CreatePostRequest;
use NickMous\Binsta\Requests\Posts\UpdatePostRequest;

class PostController extends BaseController
{
    public function __construct(
        private readonly PostRepository $postRepository,
    ) {
    }

    /**
     * Store a new post
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        $request->validate(true);

        try {
            // Create the post using the repository
            $post = $this->postRepository->create(
                title: $request->get('title'),
                description: $request->get('description'),
                code: $request->get('code'),
                programmingLanguage: $request->get('programming_language'),
                userId: $request->get('user_id')
            );

            return new JsonResponse([
                'message' => 'Post created successfully',
                'post' => $post->toArray()
            ], 201);
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('Post creation failed: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to create post',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Show a specific post
     */
    public function show(Post $post): JsonResponse
    {
        return new JsonResponse([
            'post' => $post->toArray()
        ]);
    }

    /**
     * Get all posts (with optional pagination)
     */
    public function index(): JsonResponse
    {
        try {
            // Get recent posts (you can add pagination later)
            $posts = $this->postRepository->findRecent(20);

            return new JsonResponse([
                'posts' => array_map(fn(Post $post) => $post->toArray(), $posts),
                'count' => count($posts)
            ]);
        } catch (\Exception $e) {
            error_log('Failed to fetch posts: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to fetch posts',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Update a post
     */
    public function update(Post $post, UpdatePostRequest $request): JsonResponse
    {
        $request->validate(true);

        try {
            // Update the post
            $updatedPost = $this->postRepository->update($post->getId(), [
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'code' => $request->get('code'),
                'programming_language' => $request->get('programming_language')
            ]);

            if (!$updatedPost) {
                return new JsonResponse([
                    'message' => 'Post not found'
                ], 404);
            }

            return new JsonResponse([
                'message' => 'Post updated successfully',
                'post' => $updatedPost->toArray()
            ]);
        } catch (\Exception $e) {
            error_log('Post update failed: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to update post',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Delete a post
     */
    public function destroy(Post $post): JsonResponse
    {
        try {
            $deleted = $this->postRepository->deleteById($post->getId());

            if (!$deleted) {
                return new JsonResponse([
                    'message' => 'Post not found'
                ], 404);
            }

            return new JsonResponse([
                'message' => 'Post deleted successfully'
            ]);
        } catch (\Exception $e) {
            error_log('Post deletion failed: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to delete post',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get posts by programming language
     */
    public function byLanguage(string $language): JsonResponse
    {
        try {
            $posts = $this->postRepository->findByProgrammingLanguage($language);

            return new JsonResponse([
                'posts' => array_map(fn(Post $post) => $post->toArray(), $posts),
                'language' => $language,
                'count' => count($posts)
            ]);
        } catch (\Exception $e) {
            error_log('Failed to fetch posts by language: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Failed to fetch posts',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Search posts
     */
    public function search(string $query): JsonResponse
    {
        try {
            $posts = $this->postRepository->searchPosts($query);

            return new JsonResponse([
                'posts' => array_map(fn(Post $post) => $post->toArray(), $posts),
                'query' => $query,
                'count' => count($posts)
            ]);
        } catch (\Exception $e) {
            error_log('Post search failed: ' . $e->getMessage());

            return new JsonResponse([
                'message' => 'Search failed',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
