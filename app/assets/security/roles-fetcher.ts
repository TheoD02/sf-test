import * as fs from 'fs';

const fetchRoles = async () => {
  const response = await fetch('http://localhost/api/role-fetcher');
  const data = await response.json();

  return data;
};

const createEnumFileFromRoles = (roles: string[]) => {
  const text = `enum Roles {
${roles.reduce((acc, role) => {
  acc += `  ${role} = '${role}',\n`;
  return acc;
}, '')}
}

export default Roles;`;

  console.log('Fetching roles and creating enum file');
  return fs.writeFileSync('assets/security/roles.ts', text);
}

fetchRoles().then(createEnumFileFromRoles);
