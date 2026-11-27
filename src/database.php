$sql .= ' LIMIT ' . $limit;

   $stmt = $pdo->prepare($sql);
   $stmt->execute($params);

   $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

   // Return numeric fields as JSON numbers.
   foreach ($incidents as &$incident) {
       $incident['incident_id'] = (int) $incident['incident_id'];

       foreach ([
           'persons_affected',
           'latitude',
           'longitude',
           'gps_accuracy'
       ] as $field) {
           if (
               array_key_exists($field, $incident) &&
               $incident[$field] !== null &&
               $incident[$field] !== '' &&
               is_numeric($incident[$field])
           ) {
               $incident[$field] = (float) $incident[$field];
           }
       }
   }

   unset($incident);

   jsonResponse(200, [
       'success' => true,
       'message' => 'Emergency reports retrieved successfully.',
       'count' => count($incidents),
       'data' => $incidents
   ]);

} catch (Throwable $e) {
   // Keep database and technical details in Render logs.
   error_log(
       'MDRRMO incident list API error: ' . $e->getMessage()
   );

   jsonResponse(500, [
       'success' => false,
       'message' => 'Unable to retrieve incident reports. Check the Render server logs.'
   ]);
}
