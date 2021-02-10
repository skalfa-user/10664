import { schema } from 'normalizr';

const avatar = new schema.Entity('avatars');
const user = new schema.Entity('users', { 
    avatar: avatar
}, {
    processStrategy: (value, parent) => {
        return { 
            ...value, 
            avatar: parent.avatar ? parent.avatar : null,
            matchUser: parent.id
        };
    }
});

const matchedUserSchema = new schema.Entity('matchedUsers', { 
    user: user, 
    avatar: avatar
});

export const matchedUserListSchema = new schema.Array(matchedUserSchema);
