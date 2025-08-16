export interface LikeApiResponse {
    id: string | number;
    user_id: string | number;
    post_id: string | number;
    created_at?: string;
    updated_at?: string;
}

export interface ILike {
    id: number;
    userId: number;
    postId: number;
    createdAt: Date;
    updatedAt: Date;
}

export class Like implements ILike {
    public id: number;
    public userId: number;
    public postId: number;
    public createdAt: Date;
    public updatedAt: Date;

    constructor(data: Partial<Like> = {}) {
        this.id = data.id ?? 0;
        this.userId = data.userId ?? 0;
        this.postId = data.postId ?? 0;
        this.createdAt = data.createdAt ?? new Date();
        this.updatedAt = data.updatedAt ?? new Date();
    }

    static fromApiResponse(data: LikeApiResponse): Like {
        return new Like({
            id: Number(data.id),
            userId: Number(data.user_id),
            postId: Number(data.post_id),
            createdAt: new Date(data.created_at || new Date()),
            updatedAt: new Date(data.updated_at || new Date())
        });
    }

    equals(other: Like): boolean {
        return this.id === other.id &&
               this.userId === other.userId &&
               this.postId === other.postId;
    }
}