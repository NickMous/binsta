import {defineStore} from "pinia";
import type {IUser, UserApiResponse} from "@/entities/User.ts";
import {User} from "@/entities/User.ts";

interface UserStoreState extends IUser {
    biography?: string;
    isAuthenticated: boolean;
    isLoading: boolean;
    isInitialized: boolean;
}

export const useUserStore = defineStore('user', {
    state: (): UserStoreState => ({
        id: 0, 
        name: '', 
        username: '', 
        email: '', 
        profilePicture: undefined,
        biography: undefined, 
        createdAt: new Date(), 
        updatedAt: new Date(),
        isAuthenticated: false,
        isLoading: false,
        isInitialized: false
    }),
    getters: {
        getId: (state) => state.id,
        getName: (state) => state.name,
        getUsername: (state) => state.username,
        getEmail: (state) => state.email,
        getProfilePicture: (state) => state.profilePicture,
        getCreatedAt: (state) => state.createdAt,
        getUpdatedAt: (state) => state.updatedAt,
        getIsAuthenticated: (state) => state.isAuthenticated,
        getIsLoading: (state) => state.isLoading,
        getIsInitialized: (state) => state.isInitialized,
        getUser: (state): IUser => ({
            id: state.id,
            name: state.name,
            username: state.username,
            email: state.email,
            profilePicture: state.profilePicture,
            biography: state.biography,
            createdAt: state.createdAt,
            updatedAt: state.updatedAt
        })
    },
    actions: {
        setUser(user: IUser) {
            this.id = user.id;
            this.name = user.name;
            this.username = user.username;
            this.email = user.email;
            this.profilePicture = user.profilePicture;
            this.biography = user.biography;
            this.createdAt = user.createdAt;
            this.updatedAt = user.updatedAt;
            this.isAuthenticated = true;
            this.isInitialized = true;
            this.persistUser();
        },
        setUserFromApiResponse(data: UserApiResponse) {
            const user = User.fromApiResponse(data);
            this.setUser(user);
        },
        clearUser() {
            this.id = 0;
            this.name = '';
            this.username = '';
            this.email = '';
            this.profilePicture = undefined;
            this.biography = undefined;
            this.createdAt = new Date();
            this.updatedAt = new Date();
            this.isAuthenticated = false;
            this.isInitialized = true; // Mark as initialized even when cleared
        },
        setLoading(loading: boolean) {
            this.isLoading = loading;
        },
        logout() {
            this.clearUser();
            // Remove any stored session data
            localStorage.removeItem('userId');
            localStorage.removeItem('user'); // Clean up old format
            sessionStorage.removeItem('user');
        },
        persistUser() {
            // Only store user ID in localStorage for persistence across sessions
            if (this.id > 0) {
                localStorage.setItem('userId', this.id.toString());
            } else {
                localStorage.removeItem('userId');
            }
        },
        async loadPersistedUser() {
            const userId = localStorage.getItem('userId');
            if (userId) {
                const id = parseInt(userId);
                if (id > 0) {
                    try {
                        await this.fetchUser(id);
                    } catch (error) {
                        console.error('Failed to load persisted user data:', error);
                        localStorage.removeItem('userId');
                        this.clearUser();
                    }
                }
            } else {
                this.isInitialized = true;
            }
        },
        async initializeUser(userId?: number) {
            if (this.isInitialized) return;
            
            this.setLoading(true);
            try {
                if (userId && userId > 0) {
                    await this.fetchUser(userId);
                } else {
                    await this.loadPersistedUser();
                }
            } catch (error) {
                console.error('Failed to initialize user:', error);
                this.clearUser();
            } finally {
                this.setLoading(false);
                this.isInitialized = true;
            }
        },
        async fetchUser(userId: number) {
            this.setLoading(true);
            try {
                const response = await fetch(`/api/users/${userId}`);
                if (!response.ok) {
                    throw new Error(`Failed to fetch user: ${response.status}`);
                }
                const data = await response.json();
                this.setUser(data);
                return data;
            } catch (error) {
                console.error('Failed to fetch user:', error);
                throw error;
            } finally {
                this.setLoading(false);
            }
        },
        async followUser(userId: number) {
            this.setLoading(true);
            try {
                const response = await fetch(`/api/users/${userId}/follow`, {
                    method: 'POST'
                });
                if (!response.ok) {
                    throw new Error(`Failed to follow user: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Failed to follow user:', error);
                throw error;
            } finally {
                this.setLoading(false);
            }
        },
        async unfollowUser(userId: number) {
            this.setLoading(true);
            try {
                const response = await fetch(`/api/users/${userId}/unfollow`, {
                    method: 'POST'
                });
                if (!response.ok) {
                    throw new Error(`Failed to unfollow user: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Failed to unfollow user:', error);
                throw error;
            } finally {
                this.setLoading(false);
            }
        }
    }
});

