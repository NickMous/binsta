export interface PostApiResponse {
    id: string | number;
    title: string;
    description: string;
    code: string;
    programming_language: string;
    programmingLanguage?: string;
    code_theme?: string;
    user_id: string | number;
    userId?: string | number;
    user_name?: string;
    user_username?: string;
    user_profile_picture?: string;
    original_post_id?: number | null;
    original_post_id_ref?: number | null;
    original_post_title?: string;
    like_count?: number;
    user_liked?: boolean;
    fork_count?: number;
    user_forked?: boolean;
    created_at?: string;
    createdAt?: string | Date;
    updated_at?: string;
    updatedAt?: string | Date;
}

export interface PostApiPayload {
    id: number;
    title: string;
    description: string;
    code: string;
    programming_language: string;
    code_theme?: string;
    user_id: number;
    original_post_id?: number | null;
    created_at: string;
    updated_at: string;
}

export interface IPost {
    id: number;
    title: string;
    description: string;
    code: string;
    programmingLanguage: string;
    codeTheme: string;
    userId: number;
    userName?: string;
    userUsername?: string;
    userProfilePicture?: string;
    originalPostId?: number | null;
    originalPostTitle?: string;
    likeCount: number;
    userLiked: boolean;
    forkCount: number;
    userForked: boolean;
    createdAt: Date;
    updatedAt: Date;
}

export class Post implements IPost {
    public id: number;
    public title: string;
    public description: string;
    public code: string;
    public programmingLanguage: string;
    public codeTheme: string;
    public userId: number;
    public userName?: string;
    public userUsername?: string;
    public userProfilePicture?: string;
    public originalPostId?: number | null;
    public originalPostTitle?: string;
    public likeCount: number;
    public userLiked: boolean;
    public forkCount: number;
    public userForked: boolean;
    public createdAt: Date;
    public updatedAt: Date;

    constructor(data: Partial<Post> = {}) {
        this.id = data.id ?? 0;
        this.title = data.title ?? '';
        this.description = data.description ?? '';
        this.code = data.code ?? '';
        this.programmingLanguage = data.programmingLanguage ?? '';
        this.codeTheme = data.codeTheme ?? 'github-dark';
        this.userId = data.userId ?? 0;
        this.userName = data.userName;
        this.userUsername = data.userUsername;
        this.userProfilePicture = data.userProfilePicture;
        this.originalPostId = data.originalPostId ?? null;
        this.originalPostTitle = data.originalPostTitle;
        this.likeCount = data.likeCount ?? 0;
        this.userLiked = data.userLiked ?? false;
        this.forkCount = data.forkCount ?? 0;
        this.userForked = data.userForked ?? false;
        this.createdAt = data.createdAt ?? new Date();
        this.updatedAt = data.updatedAt ?? new Date();
    }

    static fromApiResponse(data: PostApiResponse): Post {
        return new Post({
            id: Number(data.id),
            title: data.title,
            description: data.description,
            code: data.code,
            programmingLanguage: data.programming_language || data.programmingLanguage || '',
            codeTheme: data.code_theme || 'github-dark',
            userId: Number(data.user_id || data.userId),
            userName: data.user_name,
            userUsername: data.user_username,
            userProfilePicture: data.user_profile_picture,
            originalPostId: data.original_post_id ?? null,
            originalPostTitle: data.original_post_title,
            likeCount: data.like_count ?? 0,
            userLiked: data.user_liked ?? false,
            forkCount: data.fork_count ?? 0,
            userForked: data.user_forked ?? false,
            createdAt: new Date(data.created_at || data.createdAt || new Date()),
            updatedAt: new Date(data.updated_at || data.updatedAt || new Date())
        });
    }

    toApiPayload(): PostApiPayload {
        return {
            id: this.id,
            title: this.title,
            description: this.description,
            code: this.code,
            programming_language: this.programmingLanguage,
            code_theme: this.codeTheme,
            user_id: this.userId,
            original_post_id: this.originalPostId,
            created_at: this.createdAt.toISOString(),
            updated_at: this.updatedAt.toISOString()
        };
    }

    getDisplayTitle(): string {
        return this.title || 'Untitled Post';
    }

    getCodePreview(maxLength: number = 100): string {
        if (this.code.length <= maxLength) {
            return this.code;
        }
        return this.code.slice(0, maxLength) + '...';
    }

    getLineCount(): number {
        return this.code.split('\n').length;
    }

    getLanguageDisplayName(): string {
        const languageMap: Record<string, string> = {
            'javascript': 'JavaScript',
            'typescript': 'TypeScript',
            'php': 'PHP',
            'python': 'Python',
            'java': 'Java',
            'html': 'HTML',
            'css': 'CSS',
            'json': 'JSON',
            'bash': 'Bash',
            'shell': 'Shell',
            'c': 'C',
            'cpp': 'C++',
            'csharp': 'C#',
            'go': 'Go',
            'rust': 'Rust',
            'ruby': 'Ruby',
            'kotlin': 'Kotlin',
            'swift': 'Swift',
            'sql': 'SQL',
            'yaml': 'YAML',
            'xml': 'XML',
            'vue': 'Vue',
            'jsx': 'JSX',
            'tsx': 'TSX'
        };
        
        return languageMap[this.programmingLanguage] || this.programmingLanguage;
    }

    hasCode(): boolean {
        return !!this.code && this.code.trim().length > 0;
    }

    isValid(): boolean {
        return !!(this.title && this.description && this.code && this.programmingLanguage && this.userId > 0);
    }

    clone(): Post {
        return new Post({
            id: this.id,
            title: this.title,
            description: this.description,
            code: this.code,
            programmingLanguage: this.programmingLanguage,
            codeTheme: this.codeTheme,
            userId: this.userId,
            userName: this.userName,
            userUsername: this.userUsername,
            userProfilePicture: this.userProfilePicture,
            originalPostId: this.originalPostId,
            originalPostTitle: this.originalPostTitle,
            likeCount: this.likeCount,
            userLiked: this.userLiked,
            forkCount: this.forkCount,
            userForked: this.userForked,
            createdAt: new Date(this.createdAt),
            updatedAt: new Date(this.updatedAt)
        });
    }

    update(data: Partial<Post>): Post {
        const updated = this.clone();
        Object.assign(updated, data);
        updated.updatedAt = new Date();
        return updated;
    }

    equals(other: Post): boolean {
        return this.id === other.id &&
               this.title === other.title &&
               this.description === other.description &&
               this.code === other.code &&
               this.programmingLanguage === other.programmingLanguage &&
               this.userId === other.userId;
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

    getLikeCountText(): string {
        if (this.likeCount === 0) {
            return '0 likes';
        } else if (this.likeCount === 1) {
            return '1 like';
        } else {
            return `${this.likeCount} likes`;
        }
    }

    toggleLike(): Post {
        const updated = this.clone();
        updated.userLiked = !updated.userLiked;
        updated.likeCount += updated.userLiked ? 1 : -1;
        
        // Ensure like count doesn't go below 0
        if (updated.likeCount < 0) {
            updated.likeCount = 0;
        }
        
        return updated;
    }

    updateLikeStatus(userLiked: boolean, likeCount: number): Post {
        const updated = this.clone();
        updated.userLiked = userLiked;
        updated.likeCount = likeCount;
        return updated;
    }

    getForkCountText(): string {
        if (this.forkCount === 0) {
            return '0 forks';
        } else if (this.forkCount === 1) {
            return '1 fork';
        } else {
            return `${this.forkCount} forks`;
        }
    }

    updateForkStatus(userForked: boolean, forkCount: number): Post {
        const updated = this.clone();
        updated.userForked = userForked;
        updated.forkCount = forkCount;
        return updated;
    }

    isForked(): boolean {
        return this.originalPostId !== null && this.originalPostId !== undefined;
    }

    canFork(currentUserId?: number): boolean {
        return !!currentUserId;

    }
}