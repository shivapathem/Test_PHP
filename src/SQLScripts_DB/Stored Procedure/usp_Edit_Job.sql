USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Edit_Job]    Script Date: 10/07/2025 13:03:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER         PROCEDURE [dbo].[usp_Edit_Job]
@EditType 		     VARCHAR(30),
@pNetLogin           VARCHAR(30),
@FromJobID  		 INT = NULL,
@IsShiftleader       INT = 0

AS
BEGIN

   -- Edit Type Parameters are given below. 
   -- COPY               -> COPY Job to unallocated job grid
  

   SET NOCOUNT ON;
   SET DATEFORMAT YMD;

   DECLARE  @vname              VARCHAR(100),
			@vuserID            INT,
			@NewJobID           INT;


   	DECLARE @AllocationsSPID	INT,
			@AllocationsDutyID	INT,
			@DutyDate			DATE,
			@AllocationsID		INT,
			@AllocationsDutyID_UN	INT,
			@IsDutyEdited		BIT,
			@IsDutyNeedAttention	BIT;
  
   select @vname = UD_DisplayName,
		  @vuserID = UD_UserID
     from UserDetails 
	where UD_NetLogin=@pNetLogin
	
   
    BEGIN TRY
        BEGIN TRANSACTION  
		
           IF  (@EditType = 'COPY')
		    BEGIN

			 IF (@FromJobID IS NULL )
			  BEGIN
			   THROW 51000, 'From Job ID Cannot be NULL', 1;  
			  END
		 
				select @AllocationsID = AD_AllocationsID,
					   @AllocationsDutyID = AD_AllocationsDutyID,
					   @AllocationsSPID = ASP_AllocationsSPID,
					   @DutyDate = AD_DutyDate,
					   @IsDutyEdited = AD_IsDutyEdited,
					   @IsDutyNeedAttention = AD_IsEditedDutyAttention
				  FROM AllocationsDuties AD 
				INNER JOIN AllocationsJobs AJ ON AJ.AJ_AllocationsDutyID = AD.AD_AllocationsDutyID
				LEFT JOIN AllocationsScheduledPersons ASP ON ASP_AllocationsDutyID = AD_AllocationsDutyID
				WHERE AJ.AJ_AllocateJobID = @FromJobID;

				/* Get U duty ID */

				select @AllocationsDutyID_UN = AD_AllocationsDutyID				
				  FROM AllocationsDuties 
				 WHERE AD_AllocationsID = @AllocationsID 
				   AND AD_DutyType = 10
				   AND AD_DutyDate = @DutyDate;			  	 

					INSERT INTO AllocationsJobs
								([AJ_AllocationsDutyID],
								 [AJ_MasterJobID],
								 [AJ_ProgrammeID],
								 [AJ_Contact],
								 [AJ_Location],
								 [AJ_JobName],
								 [AJ_JobStartTimeSec],
								 [AJ_JobEndTimeSec],
								 [AJ_JobStartTimeUTC],
								 [AJ_JobEndTimeUTC],
								 [AJ_JobStartTimeLocal],
								 [AJ_JobEndTimeLocal],
								 [AJ_JobBGColour],
								 [AJ_JobFontColour],
								 [AJ_Comments],
								 [AJ_JobStatus],
								 [AJ_JobInfo],
								 [AJ_CreatedBy],
								 [AJ_CreatedDate],
								 [AJ_UpdatedBy],
								 [AJ_UpdatedDate],
								 [AJ_IsEditedJobAttention])
					SELECT @AllocationsDutyID_UN,
						   [AJ_MasterJobID],
						   [AJ_ProgrammeID],
						   [AJ_Contact],
						   [AJ_Location],
						   [AJ_JobName],
						   [AJ_JobStartTimeSec],
						   [AJ_JobEndTimeSec],
						   [AJ_JobStartTimeUTC],
						   [AJ_JobEndTimeUTC],
						   [AJ_JobStartTimeLocal],
						   [AJ_JobEndTimeLocal],
						   [AJ_JobBGColour],
						   [AJ_JobFontColour],
						   [AJ_Comments],
						   [AJ_JobStatus],
						   [AJ_JobInfo],
						   [AJ_CreatedBy],
						   [AJ_CreatedDate],
						   @vuserID,
						   GETUTCDATE(),
						   CASE WHEN @IsShiftleader = 1 THEN 1 ELSE 0 END
					FROM   AllocationsJobs
					WHERE  AJ_AllocateJobID = @FromJobID 
			  
            SET @NewJobID = @@Identity
			
			  INSERT INTO history
					  ( historytype,
						attributeid,
						datetime,
						userid,
						history )
				 SELECT ht.id AS historytype,
						@NewJobID AS attributeid,
						getdate(),
						@vuserID,
						'New Job created by '+@vname+' On '+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
				   FROM historytypes HT
				  WHERE historytype='AllocationJobs'	
				 
				DECLARE @ReturnValue  INT = 0 
				EXEC @ReturnValue = usp_CreateAllocationsUpdate  @AllocationsID,
																 @AllocationsDutyID_UN,
																 0,
																 0,
																 @pNetLogin
																 
				IF ( @ReturnValue > 0 )
				  BEGIN
					THROW 51000, 'Error while creating Duty update status', 1;
				  END 	

	    IF ( @@TRANCOUNT  > 0 ) 
         BEGIN
           COMMIT  TRANSACTION 
         END   
	   
		  SELECT 0 as SPExecStatus,
			     'Success' as SPMessage 
	  END
			
	END TRY
				
	BEGIN CATCH

	  IF ( @@TRANCOUNT  > 0 ) 
	   BEGIN
		ROLLBACK TRANSACTION
	   END

	 IF ( ERROR_NUMBER() < 50000 )
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
			@vuserID
			
	 	 SELECT ISNULL(@@IDENTITY,ERROR_NUMBER() ) as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage  
					
	END CATCH;
				
END