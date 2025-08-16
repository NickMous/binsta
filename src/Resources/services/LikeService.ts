interface LikeResponse {
    liked: boolean;
    like_count: number;
    message: string;
}

export class LikeService {
    private static readonly BASE_URL = '/api/posts';

    static async likePost(postId: number): Promise<LikeResponse> {
        const response = await fetch(`${this.BASE_URL}/${postId}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to like post');
        }

        return response.json();
    }

    static async unlikePost(postId: number): Promise<LikeResponse> {
        const response = await fetch(`${this.BASE_URL}/${postId}/unlike`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to unlike post');
        }

        return response.json();
    }

    static async getLikeStatus(postId: number): Promise<LikeResponse> {
        const response = await fetch(`${this.BASE_URL}/${postId}/like-status`, {
            method: 'GET',
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Failed to get like status');
        }

        return response.json();
    }

    static async toggleLike(postId: number, currentlyLiked: boolean): Promise<LikeResponse> {
        return currentlyLiked 
            ? this.unlikePost(postId)
            : this.likePost(postId);
    }
}