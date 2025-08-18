export interface CommentApiResponse {
    id: string | number;
    content: string;
    post_id: string | number;
    postId?: string | number;
    user_id: string | number;
    userId?: string | number;
    user_name?: string;
    user_username?: string;
    user_profile_picture?: string;
    created_at?: string;
    createdAt?: string | Date;
    updated_at?: string;
    updatedAt?: string | Date;
}

export interface CommentApiPayload {
    id: number;
    content: string;
    post_id: number;
    user_id: number;
    created_at: string;
    updated_at: string;
}

export interface IComment {
    id: number;
    content: string;
    postId: number;
    userId: number;
    userName?: string;
    userUsername?: string;
    userProfilePicture?: string;
    createdAt: Date;
    updatedAt: Date;
}

export class Comment implements IComment {
    public id: number;
    public content: string;
    public postId: number;
    public userId: number;
    public userName?: string;
    public userUsername?: string;
    public userProfilePicture?: string;
    public createdAt: Date;
    public updatedAt: Date;

    constructor(data: Partial<Comment> = {}) {
        this.id = data.id ?? 0;
        this.content = data.content ?? '';
        this.postId = data.postId ?? 0;
        this.userId = data.userId ?? 0;
        this.userName = data.userName;
        this.userUsername = data.userUsername;
        this.userProfilePicture = data.userProfilePicture;
        this.createdAt = data.createdAt ?? new Date();
        this.updatedAt = data.updatedAt ?? new Date();
    }

    static fromApiResponse(data: CommentApiResponse): Comment {
        return new Comment({
            id: Number(data.id),
            content: data.content,
            postId: Number(data.post_id || data.postId),
            userId: Number(data.user_id || data.userId),
            userName: data.user_name,
            userUsername: data.user_username,
            userProfilePicture: data.user_profile_picture,
            createdAt: new Date(data.created_at || data.createdAt || new Date()),
            updatedAt: new Date(data.updated_at || data.updatedAt || new Date())
        });
    }

    toApiPayload(): CommentApiPayload {
        return {
            id: this.id,
            content: this.content,
            post_id: this.postId,
            user_id: this.userId,
            created_at: this.createdAt.toISOString(),
            updated_at: this.updatedAt.toISOString()
        };
    }

    getUserDisplayName(): string {
        return this.userName || this.userUsername || `User ${this.userId}`;
    }

    getUserUsername(): string {
        return this.userUsername || '';
    }

    getRelativeTime(): string {
        const now = new Date();
        const diffInMs = now.getTime() - this.createdAt.getTime();
        const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
        const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
        const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));

        if (diffInMinutes < 1) {
            return 'just now';
        } else if (diffInMinutes < 60) {
            return `${diffInMinutes} minute${diffInMinutes !== 1 ? 's' : ''} ago`;
        } else if (diffInHours < 24) {
            return `${diffInHours} hour${diffInHours !== 1 ? 's' : ''} ago`;
        } else if (diffInDays < 7) {
            return `${diffInDays} day${diffInDays !== 1 ? 's' : ''} ago`;
        } else {
            return this.createdAt.toLocaleDateString();
        }
    }

    isValid(): boolean {
        return !!(this.content.trim() && this.postId > 0 && this.userId > 0);
    }

    clone(): Comment {
        return new Comment({
            id: this.id,
            content: this.content,
            postId: this.postId,
            userId: this.userId,
            userName: this.userName,
            userUsername: this.userUsername,
            userProfilePicture: this.userProfilePicture,
            createdAt: new Date(this.createdAt),
            updatedAt: new Date(this.updatedAt)
        });
    }

    equals(other: Comment): boolean {
        return this.id === other.id &&
               this.content === other.content &&
               this.postId === other.postId &&
               this.userId === other.userId;
    }
}