import { schema } from 'normalizr';

const matchAction = new schema.Entity('matchActions');
const avatar = new schema.Entity('avatars');

const user = new schema.Entity('users', { 
    avatar: avatar,
    matchAction: matchAction
}, {
    processStrategy: (value, parent) => {
        return { 
            ...value, 
            avatar: parent.avatar ? parent.avatar : null,
            matchAction: parent.matchAction ? parent.matchAction : null,
            bookmark: parent.id
        };
    }
});

const bookmarkSchema = new schema.Entity('bookmarks', { 
    user: user, 
    avatar: avatar, 
    matchAction: matchAction 
});

export const bookmarkListSchema = new schema.Array(bookmarkSchema);
