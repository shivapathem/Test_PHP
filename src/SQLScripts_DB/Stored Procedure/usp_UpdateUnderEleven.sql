USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_UpdateUnderEleven]    Script Date: 3/24/2026 5:13:35 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE  [dbo].[usp_UpdateUnderEleven]
@AllocationsSPID               INT,
@pNetLogin                     VARCHAR(30),
@pIsUnderElevenBreakOverride   BIT, 
@pOverrideUnderElevenHrs       INT,
@pUnderElevenComment           NVARCHAR(500),
@pRemoveOverride               INT = NULL

AS
BEGIN

    SET NOCOUNT ON

    SET DATEFORMAT YMD
	
    DECLARE @vIsUnderElevenBreakOverride   BIT 
    DECLARE @vOverrideUnderElevenHrs       INT
    DECLARE @vUnderElevenComment           NVARCHAR(500)
	DECLARE @vCalculatedUnderElevenHrs     INT
    DECLARE @vSQL                          VARCHAR(MAX)
	DECLARE @vHistory                      NVARCHAR(MAX)	
	DECLARE @updateflag                    INT = 0 
	DECLARE @vuserID                       INT	
	DECLARE @vname                         VARCHAR(100)
	DECLARE @ReturnValue					INT
	DECLARE @AllocationsID					INT
	DECLARE @AllocationsDutyID				INT
		
	BEGIN TRY

	  SELECT @vname =  UD_DisplayName,
			 @vuserID = UD_UserID
	    FROM UserDetails 
	   WHERE UD_NetLogin=@pNetLogin			
 
	  SELECT @vIsUnderElevenBreakOverride = case when ASP_UnderElevenBreakStatus = 2 then 1 else 0 end,
	         @vOverrideUnderElevenHrs     = ASP_OverrideUnderElevenHrs,
			 @vUnderElevenComment         = ASP_UnderElevenComments,
			 @vCalculatedUnderElevenHrs   = ASP_CalculatedUnderElevenHrs,
			 @AllocationsID = ASP_AllocationsID,
			 @AllocationsDutyID = ASP_AllocationsDutyID
	    FROM AllocationsScheduledPersons
	   WHERE ASP_AllocationsSPID = @AllocationsSPID	   	  

	  SET @vSQL = 'UPDATE AllocationsScheduledPersons SET '
	  SET @vHistory = ''
	  
      IF ( ISNULL(@pRemoveOverride,0) = 1 )
	   BEGIN
		SET @pIsUnderElevenBreakOverride = 0
		SET @pOverrideUnderElevenHrs = 0
		SET @vHistory = 'Override For Under 11 was removed by '+@vname+' on ' 
								   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
								   + FORMAT(Getdate(),'HH:mm')+'.'		
	   END

	  IF ( ISNULL(@vIsUnderElevenBreakOverride,0) <> ISNULL(@pIsUnderElevenBreakOverride,0)  ) 
	   BEGIN 
		 SET @updateflag = 1	
		 SET @vSQL = @vSQL+' ASP_UnderElevenBreakStatus = '
						  +cast(case when @pIsUnderElevenBreakOverride = 1 
						   THEN 2 ELSE 1 END as varchar)+', '	
		 
		  IF ( ISNULL(@pRemoveOverride,0) <> 1 )
		   SET @vHistory = 'Under11 Overridden’ by '+@vname+' on ' 
		                   + FORMAT(Getdate(),'dd/MM/yyyy')+' at '
						   + FORMAT(Getdate(),'HH:mm')+'.'		   
		  
	   END	
	   
	  IF ( ISNULL(@vOverrideUnderElevenHrs,0) <> ISNULL(@pOverrideUnderElevenHrs,0)  ) 
	   BEGIN    
		 SET @vSQL = @vSQL+' ASP_OverrideUnderElevenHrs = '+cast(@pOverrideUnderElevenHrs as varchar)+', '	
		 
		  IF ( ISNULL(@pRemoveOverride,0) <> 1 )
		   BEGIN
		    SET @vHistory = @vHistory+ CASE WHEN @updateflag =1 THEN CHAR(13) ELSE '' END
		    SET @vHistory = @vHistory+'Actual Under 11 Hours changed from ['
						 + right('0' + CAST( isnull(@vOverrideUnderElevenHrs,0) / 3600 AS varchar(2)),2) + '.'  
						 + LEFT( substring(CAST(round(cast((isnull(@vOverrideUnderElevenHrs,0) % 3600) as float)/3600 ,2) AS varchar),3,2)+'00',2)+'] To ['
						 + right('0' + CAST( isnull(@pOverrideUnderElevenHrs,0) / 3600 AS varchar(2)),2) + '.'  
						 + LEFT(substring(CAST(round(cast((isnull(@pOverrideUnderElevenHrs,0) % 3600) as float)/3600 ,2) AS varchar),3,2)+'00',2)+'] by ' 		 
  		                 +@vname+' On ' + FORMAT(Getdate(),'dd/MM/yyyy')+' at '+FORMAT(Getdate(),'HH:mm')+'.'
		   END
		 SET @updateflag = 1			 
	   END	  		

	  IF ( ISNULL(@vUnderElevenComment,'X') <> ISNULL(@pUnderElevenComment,'X')  ) 
	   BEGIN 	   
		 SET @vSQL = @vSQL+' ASP_UnderElevenComments = '''+@pUnderElevenComment+''', '	
		 SET @updateflag = 1			 
	   END	  
	      
      IF ( @updateflag = 1)
	   BEGIN

		 SET @vSQL = @vSQL+' ASP_UpdatedBy = '+ CAST(@vuserID AS VARCHAR)
						                  +', ASP_UpdatedDate = getutcdate()
							  WHERE ASP_AllocationsSPID = '+cast(@AllocationsSPID as varchar)
		 EXEC (@vSQL)		

			INSERT INTO history ( historytype,
								  attributeid,
								  HistorySubType,
								  datetime,
								  userid,
								  history )		
			SELECT ht.id AS historytype,
				   @AllocationsSPID AS attributeid,
				   'PH' AS HistorySubType,
				   getdate(),
				   @vuserID,
				   @vHistory
			  FROM HistoryTypes HT
			 WHERE historytype = 'AllocationScheduledPerson'

			EXEC @ReturnValue = usp_CreateAllocationsUpdate @AllocationsID,
												@AllocationsDutyID,
												@AllocationsSPID,
												0,
												@pNetLogin;	
			 
	   END	   
	   
		SELECT 0 AS RETURNVAL
			
	END TRY
				
	BEGIN CATCH
			
		SELECT 1 AS RETURNVAL
			
	END CATCH;			
END