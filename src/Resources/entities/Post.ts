export interface PostApiResponse {
    id: string | number;
    title: string;
    description: string;
    code: string;
    programming_language: string;
    programmingLanguage?: string;
    user_id: string | number;
    userId?: string | number;
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
    user_id: number;
    created_at: string;
    updated_at: string;
}

export interface IPost {
    id: number;
    title: string;
    description: string;
    code: string;
    programmingLanguage: string;
    userId: number;
    createdAt: Date;
    updatedAt: Date;
}

export class Post implements IPost {
    public id: number;
    public title: string;
    public description: string;
    public code: string;
    public programmingLanguage: string;
    public userId: number;
    public createdAt: Date;
    public updatedAt: Date;

    constructor(data: Partial<Post> = {}) {
        this.id = data.id ?? 0;
        this.title = data.title ?? '';
        this.description = data.description ?? '';
        this.code = data.code ?? '';
        this.programmingLanguage = data.programmingLanguage ?? '';
        this.userId = data.userId ?? 0;
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
            userId: Number(data.user_id || data.userId),
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
            user_id: this.userId,
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
            userId: this.userId,
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
}