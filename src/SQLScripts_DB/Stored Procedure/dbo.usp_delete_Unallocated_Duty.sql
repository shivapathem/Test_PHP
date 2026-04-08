USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_delete_Unallocated_Duty]    Script Date: 28/03/2022 18:53:32 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_delete_Unallocated_Duty]
	-- Add the parameters for the stored procedure here
	@DutyID				INT,
	@TeamID             INT OUTPUT,
	@DutyEditStatus		INT,
	@CurrentuserID		INT, 
	@Currentuser		VARCHAR(50) ,
	@status	            INT OUTPUT,
	@returnstring       VARCHAR(100) OUTPUT,
	@IsShiftleader      INT = NULL

AS
BEGIN

	SET NOCOUNT ON;
	
	DECLARE @JobCount    INT
	DECLARE @WeekNumber  INT
	
	 UPDATE Allocations_jobs 
	    SET isActive = 0
	  WHERE AllocationID = @DutyID 
	  
	 SET @JobCount = @@ROWCOUNT 
	  
	 IF ( @JobCount > 0 )
	  BEGIN
	   
	    	INSERT INTO History 
			          ( HistoryType,
					    UserID,
						History,
						datetime,
						AttributeID ) 
				 SELECT HT.ID,
				        @CurrentuserID,
						'Job '+AJ.JobName+' Deleted On '+ SUBSTRING ( CONVERT(VARCHAR, Getdate(), 113),1,17)+' by '+@Currentuser+'.',
						getdate(),
						AJ.ID
				   FROM Allocations_Jobs AJ
				   INNER JOIN HistoryTypes HT ON 1=1
				   WHERE AJ.AllocationID = @DutyID
				     AND HT.HistoryType = 'AllocationJobs'
	  
	  END


			 UPDATE Allocations 
			    SET isActive = 0,
				    isedited = case when @IsShiftleader = 1 then 1 else isedited end
			  WHERE ID = @DutyID 
			
	    	INSERT INTO History 
			          ( HistoryType,
					    UserID,
						History,
						datetime,
						AttributeID ) 
				 SELECT HT.ID,
				        @CurrentuserID,
						'Duty '+AL.DutyName
						+case when ISNULL(@JobCount,0) > 0 then
						' has '+cast(@JobCount as Nvarchar) +' Jobs' 
						else '' end
						+' - Deleted On ' + SUBSTRING ( CONVERT(VARCHAR, Getdate(), 113),1,17)+' by '+@Currentuser+'.',
						getdate(),
						AL.ID
				   FROM Allocations AL
				   INNER JOIN HistoryTypes HT ON 1=1
				   WHERE AL.ID = @DutyID
				     AND HT.HistoryType = 'AllocationDuty'					 

			IF ( @IsShiftleader = 1 )
			 BEGIN
			   
			   SELECT @WeekNumber = WeekNumber,
			          @TeamID  = SchedulingTeamId
			     FROM Allocations 
				WHERE ID = @DutyID
				
               EXEC usp_mod_PublishIndividulAllocations @WeekNumber,@TeamID, @DutyID	
			   
			 END

END
