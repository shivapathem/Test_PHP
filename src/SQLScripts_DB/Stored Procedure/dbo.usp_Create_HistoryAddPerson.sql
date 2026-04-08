USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Create_HistoryAddPerson]    Script Date: 12/11/2025 22:30:35 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER         PROCEDURE  [dbo].[usp_Create_HistoryAddPerson]
@AllocationsAPID     INT
AS
BEGIN

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;
    
   DECLARE    @ReturnValue		INT;
 
   
    BEGIN TRY
        BEGIN TRANSACTION  


		IF NOT EXISTS ( SELECT 1
						 FROM History HT
						INNER JOIN HistoryTypes HY ON HY.id = HT.HistoryType
						WHERE HT.AttributeID = @AllocationsAPID
						  AND HY.HistoryType = 'AllocationScheduledPersonAddnlTeam')
		 BEGIN

				INSERT INTO history
								(
									historytype,
									attributeid,
									datetime,
									userid,
									history,
									HistorySubType
								)   
							SELECT ht.id AS historytype,
								AAP_AllocationsAPID AS attributeid,
								al.AL_CreatedDate,
								AL_CreatedBy,
								'Created '
								+ ' On ' + FORMAT(AL_CreatedDate,'dd/MM/yyyy HH:mm')
								+ ' By ' + UD.UD_DisplayName 
								+ ' assigned to '+UD1.UD_DisplayName
								+ '. Duty: U'
								+'.',			   
								'PH'
						FROM Allocations AL
					INNER JOIN UserDetails UD on ud.UD_UserID = AL_CreatedBy
					INNER JOIN AllocationsAddPersons AP ON AP.AAP_AllocationsID = AL.AL_AllocationsID
					INNER JOIN UserDetails UD1 ON UD1.UD_UserID = AP.AAP_SchedulingPersonID
					INNER JOIN HistoryTypes HT (Nolock) on 1=1
					WHERE historytype = 'AllocationScheduledPersonAddnlTeam'
					  AND AAP_AllocationsAPID = @AllocationsAPID		  
		  
		 END
   
        IF ( @@TRANCOUNT  > 0 ) 
         BEGIN
           COMMIT  TRANSACTION 
         END      
  
        RETURN 0
		  
        END TRY
        
        BEGIN CATCH
                 
          ROLLBACK  TRANSACTION  
		  
		 INSERT INTO ErrorLog
		        (ErrorNumber,
				 ErrorState,
				 ErrorSeverity,
				 ErrorProcedure,
				 ErrorLine,
				 ErrorMessage,
				 ErrorDateTime,
				 UserName
				)
         SELECT ERROR_NUMBER() AS ErrorNumber,
                ERROR_STATE() AS ErrorState,
				ERROR_SEVERITY() AS ErrorSeverity,
				ERROR_PROCEDURE() AS ErrorProcedure,
				ERROR_LINE() AS ErrorLine,
				ERROR_MESSAGE() AS ErrorMessage,
				getutcdate(),
				1		  

		 RETURN 1	            
          
        END CATCH;
        
END