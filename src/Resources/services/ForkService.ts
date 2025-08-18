import type { PostApiResponse } from '@/entities/Post';

interface ForkResponse {
    message: string;
    forked_post?: {
        id: number;
        title: string;
        description: string;
        code: string;
        programming_language: string;
        user_id: number;
        original_post_id: number;
        created_at: string;
        updated_at: string;
    };
}

interface ForkStatusResponse {
    user_forked: boolean;
    fork_count: number;
}

interface ForksResponse {
    forks: PostApiResponse[];
    count: number;
}

export class ForkService {
    private static readonly BASE_URL = '/api/posts';

    static async forkPost(postId: number): Promise<ForkResponse> {
        const response = await fetch(`${this.BASE_URL}/${postId}/fork`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to fork post');
        }

        return response.json();
    }

    static async getForkStatus(postId: number): Promise<ForkStatusResponse> {
        const response = await fetch(`${this.BASE_URL}/${postId}/fork-status`, {
            method: 'GET',
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to get fork status');
        }

        return response.json();
    }

    static async getForks(postId: number): Promise<ForksResponse> {
        const response = await fetch(`${this.BASE_URL}/${postId}/forks`, {
            method: 'GET',
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to get forks');
        }

        return response.json();
    }
}