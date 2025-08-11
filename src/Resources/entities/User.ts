import {useUserStore} from "@/stores/UserStore.ts";

export interface UserApiResponse {
    id: string | number;
    name: string;
    username: string;
    email: string;
    profile_picture?: string;
    profilePicture?: string;
    biography?: string;
    created_at?: string;
    createdAt?: string | Date;
    updated_at?: string;
    updatedAt?: string | Date;
}

export interface UserApiPayload {
    id: number;
    name: string;
    username: string;
    email: string;
    profile_picture?: string;
    biography?: string;
    created_at: string;
    updated_at: string;
}

export class User {
    public id: number;
    public name: string;
    public username: string;
    public email: string;
    public profilePicture?: string;
    public biography?: string;
    public createdAt: Date;
    public updatedAt: Date;

    constructor(data: Partial<User> = {}) {
        this.id = data.id ?? 0;
        this.name = data.name ?? '';
        this.username = data.username ?? '';
        this.email = data.email ?? '';
        this.profilePicture = data.profilePicture;
        this.biography = data.biography;
        this.createdAt = data.createdAt ?? new Date();
        this.updatedAt = data.updatedAt ?? new Date();
    }

    static fromApiResponse(data: UserApiResponse): User {
        return new User({
            id: Number(data.id),
            name: data.name,
            username: data.username,
            email: data.email,
            profilePicture: data.profile_picture || data.profilePicture,
            biography: data.biography,
            createdAt: new Date(data.created_at || data.createdAt || new Date()),
            updatedAt: new Date(data.updated_at || data.updatedAt || new Date())
        });
    }

    toApiPayload(): UserApiPayload {
        return {
            id: this.id,
            name: this.name,
            username: this.username,
            email: this.email,
            profile_picture: this.profilePicture,
            biography: this.biography,
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
            biography: this.biography,
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
               this.profilePicture === other.profilePicture &&
               this.biography === other.biography;
    }
}

export function refreshUser(): IUser {
    const userStore = useUserStore();

    fetch(`/api/users/${userStore.getId}`)
        .then(response => response.json())
        .then(data => {
            userStore.setUser(data)
        })
        .catch(error => {
            console.error('Failed to fetch user:', error)
            userStore.clearUser()
        });

    return userStore.getUser;
}

export interface IUser {
    id: number;
    name: string;
    username: string;
    email: string;
    profilePicture?: string;
    biography?: string;
    createdAt: Date;
    updatedAt: Date;
}