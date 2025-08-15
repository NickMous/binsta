export interface UserFollowApiResponse {
    id: string | number;
    follower_id: number;
    following_id: number;
    created_at: string;
    createdAt?: string | Date;
}

export interface UserFollowApiPayload {
    id: number;
    follower_id: number;
    following_id: number;
    created_at: string;
}

export class UserFollow {
    public id: number;
    public followerId: number;
    public followingId: number;
    public createdAt: Date;

    constructor(data: Partial<UserFollow> = {}) {
        this.id = data.id ?? 0;
        this.followerId = data.followerId ?? 0;
        this.followingId = data.followingId ?? 0;
        this.createdAt = data.createdAt ?? new Date();
    }

    static fromApiResponse(data: UserFollowApiResponse): UserFollow {
        return new UserFollow({
            id: Number(data.id),
            followerId: data.follower_id,
            followingId: data.following_id,
            createdAt: new Date(data.created_at || data.createdAt || new Date())
        });
    }

    toApiPayload(): UserFollowApiPayload {
        return {
            id: this.id,
            follower_id: this.followerId,
            following_id: this.followingId,
            created_at: this.createdAt.toISOString()
        };
    }

    isValid(): boolean {
        return this.followerId > 0 && this.followingId > 0 && this.followerId !== this.followingId;
    }

    clone(): UserFollow {
        return new UserFollow({
            id: this.id,
            followerId: this.followerId,
            followingId: this.followingId,
            createdAt: new Date(this.createdAt)
        });
    }

    equals(other: UserFollow): boolean {
        return this.id === other.id &&
               this.followerId === other.followerId &&
               this.followingId === other.followingId;
    }
}

export interface IUserFollow {
    id: number;
    followerId: number;
    followingId: number;
    createdAt: Date;
}