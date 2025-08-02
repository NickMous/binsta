export class User {
    public id: number;
    public name: string;
    public username: string;
    public email: string;
    public profilePicture?: string;
    public createdAt: Date;
    public updatedAt: Date;

    constructor(data: Partial<User> = {}) {
        this.id = data.id ?? 0;
        this.name = data.name ?? '';
        this.username = data.username ?? '';
        this.email = data.email ?? '';
        this.profilePicture = data.profilePicture;
        this.createdAt = data.createdAt ?? new Date();
        this.updatedAt = data.updatedAt ?? new Date();
    }

    static fromApiResponse(data: any): User {
        return new User({
            id: Number(data.id),
            name: data.name,
            username: data.username,
            email: data.email,
            profilePicture: data.profile_picture || data.profilePicture,
            createdAt: new Date(data.created_at || data.createdAt),
            updatedAt: new Date(data.updated_at || data.updatedAt)
        });
    }

    toApiPayload(): Record<string, any> {
        return {
            id: this.id,
            name: this.name,
            username: this.username,
            email: this.email,
            profile_picture: this.profilePicture,
            created_at: this.createdAt.toISOString(),
            updated_at: this.updatedAt.toISOString()
        };
    }

    getDisplayName(): string {
        return this.name || this.username;
    }

    hasProfilePicture(): boolean {
        return !!this.profilePicture;
    }

    isValid(): boolean {
        return !!(this.name && this.username && this.email);
    }

    clone(): User {
        return new User({
            id: this.id,
            name: this.name,
            username: this.username,
            email: this.email,
            profilePicture: this.profilePicture,
            createdAt: new Date(this.createdAt),
            updatedAt: new Date(this.updatedAt)
        });
    }

    update(data: Partial<User>): User {
        const updated = this.clone();
        Object.assign(updated, data);
        updated.updatedAt = new Date();
        return updated;
    }

    equals(other: User): boolean {
        return this.id === other.id &&
               this.name === other.name &&
               this.username === other.username &&
               this.email === other.email &&
               this.profilePicture === other.profilePicture;
    }
}

export interface IUser {
    id: number;
    name: string;
    username: string;
    email: string;
    profilePicture?: string;
    createdAt: Date;
    updatedAt: Date;
}