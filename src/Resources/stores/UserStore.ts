import {defineStore} from "pinia";

export const useUserStore = defineStore('user', {
    state: (): IUser => ({id: 0, name: '', username: '', email: '', profilePicture: undefined, createdAt: new Date(), updatedAt: new Date()}),
    getters: {
        getId: (state) => state.id,
        getName: (state) => state.name,
        getUsername: (state) => state.username,
        getEmail: (state) => state.email,
        getProfilePicture: (state) => state.profilePicture,
        getCreatedAt: (state) => state.createdAt,
        getUpdatedAt: (state) => state.updatedAt,
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
        },
        clearUser() {
            this.id = 0;
            this.name = '';
            this.username = '';
            this.email = '';
            this.profilePicture = undefined;
            this.createdAt = new Date();
            this.updatedAt = new Date();
        }
    }
});

export interface IUser {
    id: number;
    name: string;
    username: string;
    email: string;
    profilePicture?: string;
    createdAt: Date;
    updatedAt: Date;
}