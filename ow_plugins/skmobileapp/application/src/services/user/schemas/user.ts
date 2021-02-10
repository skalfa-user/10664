import { schema } from 'normalizr';
import omit from 'lodash/omit';

const bookmark = new schema.Entity('bookmark');
const matchAction = new schema.Entity('matchAction');
const avatar = new schema.Entity('avatar');
const photo  = new schema.Entity('photos');
const permission = new schema.Entity('permissions', {}, {
    processStrategy: (value, parent) => {
        return { 
            ...omit(value, [
                'user'
            ]), 
            userId: parent.id
        };
    }
});

export const userSchema = new schema.Entity('user', {
    avatar: avatar,
    photos: [ photo ],
    matchAction: matchAction,
    bookmark: bookmark,
    permissions: [ permission ]
}); 
