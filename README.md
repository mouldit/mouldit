<h1>Mouldit</h1>
<p>Mouldit strives to make creating enterprise ready CRUD applications easier and faster at the same time. It does this with the help of a CLI, which let's you setup all necessary rest API's together with the UI that needs to interact with them. The configuration path you follow through this CLI will make it possible to get as custom as you need, not only regarding your rest API's but regarding the frontend of your app as well. At the same time for every configuration, default behaviour is built-in as to limit the specifications you need to make.</p>
<h2>The Mouldit CLI</h2>
<p>With the CLI you can configure actions and UI components that will trigger these actions. When you have ended the steps in the CLI, Mouldit will generate you application based on this configuration as well as on the database schema you have manually added to your project.</p>
<h3>Database schema</h3>
Mouldit only works with an edgeDB project. It will use the types that can be found in your edgeQL schema to determine all possible server actions. <a href="https://www.edgedb.com">Here</a> you can find information on how to create an edgeDB project and the neceassry schema. <a href="https://www.mouldit.io">Here</a> you can find an example on how such a schema and the CLI work together to create your application.
<h3>Server actions</h3>
<p>These type of actions represent all rest API endpoints that a client can send a request to. Each server action represents one rest API. At the moment the following type of actions are possible (a lot more will be added in the future!):
<ul>
 <li>Get one</li>
 <li>Get all</li>
 <li>Add one to list</li>
 <li>Remove one from list</li>
</ul>
All these actions represent either a query or a mutation. Queries are all actions that starts with 'Get'.
</p>
<h4>Queries</h4>
<p>A query represents a get request. A query has five parts you can configure:
<ul>
 <li>concept</li>
 <li>filter</li>
 <li>exclude/include (sub)field(s)</li>
 <li>transform fieldvalue(s)</li>
 <li>calculated (sub)field(s)</li>
</ul>
</p>
<h5>Concept</h5>
<p>
 
</p>
<h5>Filter</h5>
<p>
 
</p>
<h5>Field configurations</h5>
<p>
 
</p>
<h4>Mutations</h4>
<h2>Gradual approach</h2>
<p>Although the goal is to make the CLI so that you don't need to add any custom code after the initial setup, this will only be achieved gradually. As Mouldit grows the amount of actions will get bigger as well as the level of detail to which you can configure these actions. What the frontend concerns, there it will be the amount of UI components and their level of customization.</p>
