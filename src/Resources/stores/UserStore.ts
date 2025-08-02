import {defineStore} from "pinia";
import type {IUser, UserApiResponse} from "@/entities/User.ts";
import {User} from "@/entities/User.ts";

interface UserStoreState extends IUser {
    isAuthenticated: boolean;
    isLoading: boolean;
}

export const useUserStore = defineStore('user', {
    state: (): UserStoreState => ({
        id: 0, 
        name: '', 
        username: '', 
        email: '', 
        profilePicture: undefined, 
        createdAt: new Date(), 
        updatedAt: new Date(),
        isAuthenticated: false,
        isLoading: false
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
        getUser: (state): IUser => ({
            id: state.id,
            name: state.name,
            username: state.username,
            email: state.email,
            profilePicture: state.profilePicture,
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
            this.createdAt = user.createdAt;
            this.updatedAt = user.updatedAt;
            this.isAuthenticated = true;
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
            this.createdAt = new Date();
            this.updatedAt = new Date();
            this.isAuthenticated = false;
        },
        setLoading(loading: boolean) {
            this.isLoading = loading;
        },
        logout() {
            this.clearUser();
            // Remove any stored session data
            localStorage.removeItem('user');
            sessionStorage.removeItem('user');
        },
        persistUser() {
            // Store user data in localStorage for persistence across sessions
            const userData = {
                id: this.id,
                name: this.name,
                username: this.username,
                email: this.email,
                profilePicture: this.profilePicture,
                createdAt: this.createdAt.toISOString(),
                updatedAt: this.updatedAt.toISOString()
            };
            localStorage.setItem('user', JSON.stringify(userData));
        },
        loadPersistedUser() {
            const userData = localStorage.getItem('user');
            if (userData) {
                try {
                    const parsedData = JSON.parse(userData);
                    this.setUser({
                        ...parsedData,
                        createdAt: new Date(parsedData.createdAt),
                        updatedAt: new Date(parsedData.updatedAt)
                    });
                } catch (error) {
                    console.error('Failed to load persisted user data:', error);
                    localStorage.removeItem('user');
                }
            }
        }
    }
});

